# Audit report — M4 product audit: database / catalog surfaces (pass 2)

## 1. Identity
- Audit id: AUD-20261102-004 (re-issue of AUD-20261101-004, same surface set)
- Date: 2026-11-02. Pass 1 was 2026-11-01 and its record is preserved at §11 below.
- Pass-3 addendum, same day (2026-11-02, 18:26–18:47 UTC): the coverage pass 2 admitted it had not
  walked is now walked — 390 and 768 viewports, 200% text resize, the SC 1.4.12 text-spacing override,
  SC 2.4.11 focus-not-obscured, and the `eva01` theme. It produced **one new finding (F-13, a WCAG
  1.4.10 reflow failure on `/support-cards`) and one retraction**: pass 2's page-level dark-theme
  contrast claim ("min 4.83, clean") was measured by an instrument that cannot measure what it
  claimed, so it is withdrawn and replaced by the repository's own three-theme gate (§4). §2, §3, §4,
  §5, §6, §7 and §8 carry the changes; nothing in §5's other rows is contradicted by the new pass.
- Mode: M4 product audit, with the A11y overlay run as a separate track, plus one owner-ordered
  content-accuracy track (see the scope contract at §3).
- Scope: 13 surfaces served live at `http://127.0.0.1:8000`, all re-checked against source this run:
  `/database` (hub), `/umamusume` + `/umamusume/{slug}`, `/support-cards` + `/support-cards/{id}`,
  `/skills` + `/skills/{id}?from=…`, `/database/{trainees,supports,skills,races,scenarios,events,shop-items,sparks,sources}`.
  Detail routes walked: `/umamusume/admire-vega`, `/support-cards/187`, `/skills/370?from=%2Fskills`.
- Out of scope: career/cockpit/wizard/review/legacy surfaces; the app shell's own navigation audit
  (rail and breadcrumb are in scope only where an in-scope page depends on them); backend write paths
  (there are none on these screens); performance; AAA criteria; any code fix.
- Frameworks applied: `design-framework-index-2026-10-21.md` first, then, read from the files rather
  than from memory, `usability-heuristics`, `shneiderman-golden-rules`, `tognazzini-first-principles`,
  `gerhardt-powals-principles`, `information-architecture-principles`, `gestalt-principles`,
  `norman-interaction-principles`, `laws-of-ux`, `uilaws`, `wcag-2.2-accessibility`. Routing followed
  the §4 table of the operating prompt: narrowest rule per question, one finding per defect.
- Rendered evidence: **TRUE**. Chromium launched through the Playwright MCP against the running dev
  server. This is the single largest difference from pass 1, which recorded `spawn EPERM` and left
  every visual claim UNMEASURED. Screenshots were not retained (the MCP writes them outside the
  repository, and AGENTS.md §9 forbids leaving artifacts outside the tree), so every rendered claim
  below is a DOM/CSSOM measurement quoted as numbers, not a frame.
- Content cross-check: `docs/UMAMUSUME_REFERENCE.md` (anchor 2026-09-27; structural pass 2026-10-02;
  live-ops passes 2026-10-05/2026-10-20; integration 2026-10-15; latest dated correction in the file
  2026-11-01) read against the live display path for races §1.2, scenarios §1.6.1/§2.1, events §1.4.5/§4,
  shop items §1.6.10, sparks §1.5.2/§1.5.3, support cards §1.4.2, sources §7 and the tier scale,
  terminology §6, and the conflict log rows 2, 7, 10, 16, 31, 47, 51, 52, 55, 56.

## 2. Executive summary
- **The two pass-1 findings are both true against `HEAD` and both already fixed in the working tree by
  an uncommitted peer edit — and the fix is not built.** `git show HEAD:resources/js/pages/Database/Index.vue`
  has no `area.source` line; `git diff HEAD` adds exactly one line that renders it. The served bundle
  (`public/build/manifest.json`, 22:54) predates the edit (23:36), so the live hub still renders tiles
  as `label \| count \| description` with no provenance word. Same shape on Scenarios: the tree wraps
  "the Global prose sources" in a `<Link :href="route('database.sources')">`; the live page does not
  carry the link. F-01 and F-02 stay open until those edits are built and landed.
- **Lead confirmed finding (F-04, severity 2, High):** the source ledger prints one shared timestamp
  (`2026-10-08 14:44:14`) as the `READ` date of eight different fetched sources, and `data_sources`
  holds rows for only one of them (`gametora-characters`, 67 rows). The artwork mirror is listed as
  Fetched with that date although, per `AGENTS.md` §8 / `ADR-0021`, it writes nothing to the database.
  The ledger's own hub tile promises "the date each was read". Cited to `TOG-3`: status may be absent,
  never wrong.
- **New in pass 3 (F-13, severity 2, High, and the only normative WCAG AA failure this audit found):**
  `/support-cards` scrolls the whole document sideways at phone widths — 248px at 320, 178px at 390,
  612px at 200% text — and **one visually-hidden table-header label causes all of it.** Hiding that one
  `span.sr-only` ("Open the card", `resources/js/pages/SupportCards/Index.vue:285`) takes the document
  overflow to exactly 0 at both widths; the shared component behind `/database/supports` does not have
  the defect, and the other eleven surfaces measured 0 overflow at 320. One line of CSS fixes it (§8).
- **Retracted in pass 3:** every page-level dark-theme contrast number in this report, including pass
  2's "dark came back clean (min 4.83)". The method they were taken with — writing
  `data-theme` on `<html>` and sampling after a settle — does not produce a valid dark-theme frame on
  these pages: on a fresh `/umamusume` load, setting `dark` leaves `a.card` at `rgb(255,255,255)`
  while its own `--color-panel` resolves `#2a2a2a` and its title text goes white (an apparent 1.00:1),
  and removing the attribute leaves the card at `rgb(42,42,42)` in the light theme. The computed
  surface lags the token by one toggle in both directions, so those readings measured a transition,
  not a theme — in either direction. What replaces them is the repository's own gate, which resolves
  the tokens rather than sampling painted elements (§4).
- **The content track came back mostly clean, and the clean parts are load-bearing.** The four scenario
  cap rows, their `live_on_global` dates, `base_cap` 1200 and `hard_cap` 2000, the per-grade race
  payouts, the blue-spark `+5/+12/+21` ladder, the `+16` ★3 cap raise, the three star-roll odds bands,
  the hint-discount ladder 10/20/30/35/40, the SR cap 45 with four breaks at +5, and the 500/550
  star-breakthrough totals all match `docs/UMAMUSUME_REFERENCE.md` line for line (§5 verification set).
  Both dated row-count errata the reference wrote *for these very views* (`SCR-SYS-009` ten-rows/
  four-match, `SCR-SYS-010` 806-described/874-export) are still printed on the shipped pages.
- **Three findings are asymmetries of disclosure, not missing data.** The races screen marks the
  contested 1400 m band on every affected row and in a footnote, yet prints the single-domain `Pre-OP`
  grade label and the derived payout table with no provenance line at all (F-05, F-06). The catalog
  excludes four non-game rows from its hub count but still lists and links them as trainees (F-08).
  The mechanism exists; it is not applied everywhere it is needed.
- Largest risk now: the **page-level dark and `eva01` rendering of the catalog and support-card
  surfaces cannot be measured with the tool this audit had**, because forcing the theme attribute on a
  live SPA session does not converge (above). The token-pair gate covers all three themes and passes;
  a real per-page dark walk needs the `/preferences` row set through the app, which is a write to the
  shared dev database and therefore outside a read-only audit. Still open: 400% zoom, a full
  keyboard traversal, and the loading frame.

## 3. Scope and coverage
**Scope contract.** Mode M4 with an accessibility overlay and one owner-ordered content track. The
operating prompt normally places content strategy out of scope; the owner ordered it explicitly for
this surface set ("include audit for contents displayed in these screens that are directly related to
docs/UMAMUSUME_REFERENCE.md contents as the main source of information"). Content findings are
therefore filed with the reference line as the authority and the framework rule that decides the
user-facing consequence, and they are labelled in the Finding column so no reader mistakes a content
discrepancy for a heuristic preference.

Covered:
- Surfaces: all 13 named at §1, plus the 404 document as the front door (`/database/nope`).
- States: populated (all 13); empty (`/database/races?q=zzqxj`, `/database/trainees?search=Form%20Counted`);
  error (404); loading — recorded as **not applicable at source level** (see §6 for what that excludes).
- Themes: **light only, at fresh page load.** Pass 2 recorded dark as measured after a settle; pass 3
  withdraws that (see §4) — a forced `data-theme` write on a live SPA session does not converge on
  these pages, so no page-level dark or `eva01` figure in this report is admissible. The three-theme
  token-pair gate `G-18` was run instead and passes (§4).
- Viewports: 320, 390, 768 and desktop (1280×900), all walked. Reflow was measured as
  `documentElement.scrollWidth - clientWidth` **plus** an attempted `scrollLeft` to prove the scroll is
  real, on eleven of the thirteen surfaces at 320, on six at 390, and on all six re-tested at 768.
  Every surface fits at 768 and at desktop; one surface fails at 320 and 390 (F-13).
- Text enlargement: 200% (root `font-size`) measured on `/support-cards`; the SC 1.4.12 text-spacing
  override measured on the same page. 400% zoom not run.
- Keyboard: Tab from document start on `/umamusume/admire-vega` and `/database/races`. Measured: the
  skip link is 1×1 unfocused and 137×40 at `top:16 left:16` when focused; the focused link's ring is
  `2px solid rgb(39, 86, 250)` at `outline-offset: 2px` (5.53:1 against the light surface, so SC
  1.4.11 holds). One sticky region exists at desktop (`ASIDE` rail, full height, beside the content,
  `z-index: auto`) and no focused element was found beneath it, so SC 2.4.11 shows no failure on the
  two surfaces tested. A full traversal of each surface was not run.

Framework coverage matrix:

| Framework | Rules applied | N/A / not reached |
|---|---|---|
| WCAG 2.2 AA | WCAG-1..8 as the conformance track: 1.3.1, 1.4.1, 1.4.3 (light, fresh-load only), 1.4.10 (**measured at 320/390/768/desktop and at 200% text — one failure, F-13**), 1.4.11, 1.4.12 (measured on `/support-cards`), 2.1.1, 2.4.1, 2.4.6, 2.4.7, 2.4.11 (measured on two surfaces), 2.5.8, 3.3.1, 3.3.2, 4.1.2 | 1.4.4 at 400% zoom UNMEASURED; page-level dark/`eva01` contrast **not admissible by this instrument** (§4) — the `G-18` token-pair gate covers all three themes and passes; 2.5.7 N/A (no drag); 3.2.6/3.3.7 N/A (read-only, no repeated entry); 4.1.1 not cited (WCAG-7) |
| Heuristics | H1, H2, H4, H5, H6, H9 | H3 no write path to reverse on these screens; H7 and H10 system gaps, §7 |
| Shneiderman | SHR-1, SHR-4(N/A: no multi-step flow here), SHR-5(N/A), SHR-6(N/A), SHR-7 | SHR-2 (no expert path to serve on read-only); SHR-8 **does not exist in the file** — see §7 |
| Tognazzini | TOG-3, TOG-5, TOG-8 | TOG-1 (50 ms acknowledgement) UNMEASURED; TOG-2, TOG-4, TOG-6, TOG-7 N/A on read-only surfaces |
| Gerhardt-Powals | GP-1, GP-2, GP-3 | GP-4, GP-5 not reached |
| Information architecture | IA-1, IA-3, IA-4, IA-5, IA-6 | IA-2 (nav banding) reviewed, no finding |
| Gestalt | GT-1, GT-2, GT-4, GT-6 | GT-3, GT-5 not reached |
| Norman | NRM-1, NRM-2, NRM-6 | NRM-3, NRM-4, NRM-5 not reached |
| Laws of UX | UX-1, UX-3, UX-4, UX-5, UX-9, UX-10 | UX-2, UX-6, UX-7, UX-8 not reached |
| Laws of UI | UI-1, UI-2, UI-3, UI-4, UI-6 | UI-5, UI-7 not reached |

## 4. Evidence status and limits
- **Rendered evidence capability: TRUE.** Chromium launched; DOM and computed-style measurements were
  taken live against `http://127.0.0.1:8000`. No `spawn EPERM` was hit this run.
- **Instrument limit found in pass 3, and it invalidates the dark-theme readings of both passes.**
  This audit's only way into the dark and `eva01` palettes was writing the same `data-theme`
  attribute the app writes (the `/preferences` route would be a write to the shared dev database,
  which a read-only audit must not make). On a fresh `/umamusume` load with a 1200 ms settle,
  setting `dark` leaves `a.card` at `rgb(255,255,255)` while the very same element's `--color-panel`
  resolves `#2a2a2a` and its title colour goes white — an apparent ratio of 1.00:1; removing the
  attribute then leaves the card at `rgb(42,42,42)` in the light theme. The painted surface lags the
  token by one step in both directions, so **any page-level contrast number taken this way is
  inadmissible, including pass 2's "dark min 4.83, clean" and the 24/27-item dark failure lists an
  intermediate run produced.** What was kept: light-theme readings taken at fresh page load with no
  toggle at all (the `◇` 3.38:1 of F-09 and the hub `i` 3.89:1 are of that kind), and every
  geometric measurement (boxes, offsets, scroll widths), none of which depends on the palette.
- What replaces the unmeasurable: the repository's own three-theme gate was run, and it resolves
  tokens rather than sampling painted elements — `php artisan test --filter='browser contrast'` →
  `Tests: 3 passed (357 assertions)`, `EXIT=0`. `contrastThemes()`
  (`tests/Feature/DesignTokensTest.php:624-627`) loops `light`, `dark` and `eva01` over the 41
  contract pairs and 9 badge tokens, so all three palettes clear 4.5:1 (text) and 3:1 (non-text)
  **at token-pair level**. A per-page dark-theme paint walk remains open; it needs the theme set
  through the app itself.
- Earlier transition caveat, kept because it is the same trap in milder form: `.card` carries
  `transition-colors duration-150` (`resources/js/pages/Database/Index.vue:25`), so a flip sampled
  too early returns an animating background under an already-flipped colour. The settle was necessary
  but, as the bullet above shows, not sufficient.
- Bundle-vs-tree limit: `public/build/manifest.json` is stamped 22:54 and both dirty Database pages
  were edited at 23:36/23:37. Rendered observations therefore describe `HEAD` markup where a peer has
  already edited the source. Stated per finding rather than silently resolved either way.
- `OBSERVED_RENDERED` claims: the hub tile text set; the ledger rows and their shared timestamp; the
  subnav and rail target boxes; the breadcrumb box 51×16; the focused skip link 137×40 with a
  `2px solid rgb(39, 86, 250)` ring at `outline-offset: 2px`; the `◇` marker colour
  `rgb(140, 138, 154)` on `rgb(255, 255, 255)` at 3.38:1; font-size sets per surface; the empty-state
  copy on `/database/races?q=zzqxj`; the four `Form Counted` rows and their URLs; the 404 document.
- `OBSERVED_SOURCE` claims: all `file:line` citations, verified by reading the file at that line this
  run (no line number in this report is carried over from pass 1 unverified).
- Prior measurements cited: none from pass 1 (it ran with `RENDERED_EVIDENCE = FALSE`).
- Provenance caveats carried from the framework files (§3.1 of the operating prompt): Gerhardt-Powals
  primary paywalled, nine items in the secondary deck against ten in the file, items 5 and 6 unconfirmed
  as separate principles; Dan Brown's prose unread (the applied commentary is this repository's reading);
  Norman's book unread (quotations from the encyclopedia entry, the six-principle set is convention);
  Tognazzini's list is the original edition's sixteen and the 2014 revision truncated unread; `laws-of-ux`
  carries 29 entries against 28 in the index; `uilaws` carries 13 and publishes Closure and Continuity.
- Attribution corrections applied: Closure, Continuity and the partial-indicator rules cited to
  `uilaws`/Gestalt, never to `laws-of-ux`; Common Fate cited to Gestalt; Fitts and Hick cited to
  `laws-of-ux`; the 44px figure cited as the **house rule exceeding** WCAG 2.5.8, never as 2.5.8
  conformance; no "Law of Experience" (that is Mental Model, Laws of UX 17); 4.1.1 not cited.

## 5. Findings

| ID | Rule ID | Framework | Surface / state | Observation | Evidence status | Evidence location | Sev | Conf | Recommendation |
|---|---|---|---|---|---|---|---|---|---|
| F-01 | UX-4 (primary), H1 | Laws of UX | `/database` hub, populated, both themes | `DatabaseController::index` supplies `source` per area ('Fetched' / 'Transcribed' / 'Config'), `DatabaseController.php:69-80`. At `HEAD` the tile template renders `label`, `count`/`tag`, `description` and no `source`; measured on the live page every tile reads `Trainees \| 67 \| Aptitudes, costumes, base stats and growth rows.` with no provenance word. The working tree already adds the line (`git diff HEAD` = +1, `resources/js/pages/Database/Index.vue:88`), but the served bundle predates it, so the fix is not yet visible or landed. | OBSERVED_RENDERED + OBSERVED_SOURCE | `app/Http/Controllers/DatabaseController.php:69-80`; `resources/js/pages/Database/Index.vue:88` (uncommitted); `git show HEAD:resources/js/pages/Database/Index.vue` | 2 | High | Build and land the existing one-line edit; acceptance: each hub tile prints its `Fetched` / `Transcribed` / `Config` word beside the count. Do not re-implement it. |
| F-02 | SHR-7 (primary), H2 | Shneiderman | `/database/scenarios`, populated, both themes | The caps note still reads "Verified 2026-09-27 against the GameTora scenarios.json export and the Global prose sources the config header names" with no reachable target from the claim itself: the live note element contains zero `<a>` (measured). `DatabaseNav` does reach `/database/sources` from the same page, so the ledger is one click away but the verification statement is not checkable in place. The tree already wraps "the Global prose sources" in a `<Link :href="route('database.sources')">` (uncommitted, unbuilt). | OBSERVED_RENDERED + OBSERVED_SOURCE | `resources/js/pages/Database/Scenarios.vue:191-198` (uncommitted `+8/-4`); `components/database/DatabaseNav.vue:28` | 2 | High | Build and land the tree edit; acceptance: the phrase "the Global prose sources" is a link to `/database/sources`. |
| F-03 | UX-9 (primary), H1, IA-4 | Laws of UX | `/database` hub vs the four counted doors, populated | Three of the four counted tiles advertise a figure the door contradicts. Hub `Skills 1,910` (raw `select count(*) from skills`, `DatabaseController.php:53`) against `/database/skills` and `/skills`, both of which scope to `Skill::availableOnGlobal` (`SkillController.php:331-332`) and render `Showing 1–25 of 623 skills` with no mention of 1,910 anywhere on the page. Hub `Trainees 67` (`…from umamusume where external_ref is not null`, `:52`) against both trainee doors, which render `Showing 1–6 of 71 trainees`. Hub `Support Cards 559` against a door defaulting to `server = Global` (251), though that page does print both numbers ("of 251 cards" and "of 559 support cards"), which is the pattern the other two lack. The hub's own subtitle promises "where each figure came from". | OBSERVED_RENDERED + OBSERVED_SOURCE | `app/Http/Controllers/DatabaseController.php:51-56`; `app/Http/Controllers/SkillController.php:84-118,331-332`; live `/database/skills`, `/database/trainees`, `/database/supports` totals | 2 | High | Put the scope in the number, the way `/database/supports` already does: either count what the door shows, or print both ("623 on Global · 1,910 recorded"). Effort S. Acceptance: no hub tile's figure is unreachable from its own page. |
| F-04 | TOG-3 (primary), UX-4, GP-2 | Tognazzini | `/database/sources`, populated, both themes | Every `Fetched` row shows the identical `READ` value `2026-10-08 14:44:14` (nine rows measured: characters, character-cards, race-catalog, skills, character-profiles, support-cards, support-effects, artwork, ura-objectives). The controller reads one global maximum, `DB::table('data_sources')->max('fetched_at')` (`DatabaseController.php:420`), and assigns it to each row (`:432`). Queried directly, `data_sources` holds 67 rows and **all of them carry `source_key = 'gametora-characters'`**, so the printed date is that one source's own maximum and belongs to no record at all for the other eight. `gametora-artwork` is the clearest case: `AGENTS.md` §8 and `ADR-0021` state that `uma:fetch-art` writes nothing to the database and no `data_sources` row, yet the ledger prints it as Fetched with a read timestamp. The row note does disclose the mechanism ("The newest recorded fetch across every dataset is the date beside this row"), and the hub tile for this area promises "Every source, the date each was read, and what it fed." Status may be stale or absent; it must not be wrong. | OBSERVED_RENDERED + OBSERVED_SOURCE | `app/Http/Controllers/DatabaseController.php:418-436`; live `/database/sources` row set; `data_sources` group-by on `database/database.sqlite`; `AGENTS.md` §8 Artwork bullet | 2 | High | Read each source's own newest `data_sources.fetched_at` and print `No fetch recorded` where there is none (artwork and the seven other keys with no row). Effort S, one query changed to a grouped max. Acceptance: at least two rows show different dates, and `gametora-artwork` shows no date. |
| F-05 | GP-2 (primary), TOG-3, UI-4 | Gerhardt-Powals | `/database/shop-items`, Trackblazer table, populated | The row reads `Artisan Cleat Hammer \| 25 \| Race bonus +20%, 1 turn \| Must Buy` in the DOM (reached through the page's `Show all` control: 19 rows, 10 shown by default), so the cost is printed as a stated fact. The authority gives that same item two figures: `docs/UMAMUSUME_REFERENCE.md:156` — "the same page gives the Trackblazer Pro-Shop hammers as Race stat gain +20% (Artisan, 20 Shop Coins)" — and `:2524` — `"Artisan Cleat Hammer" (25 coins)`. The app already owns two devices for exactly this situation: the `Open conflicts` card on `/database/sources` (`Sources.vue:68-72`, three entries) and the per-row asterisk for a contested figure (`Races.vue:296-301`). Neither carries the hammer price, so the screen prints one side of a live disagreement in its own source document with no mark. | OBSERVED_RENDERED + OBSERVED_SOURCE | `config/scenarios.php:755`; `docs/UMAMUSUME_REFERENCE.md:156`, `:2524`; `resources/js/pages/Database/Sources.vue:68-72`; `resources/js/pages/Database/Races.vue:296-301` | 2 | High | Either add the price to the `Open conflicts` register with both publisher values, or footnote the row the way the 1400 m row is footnoted. Effort S. Acceptance: a reader can see, on the shop page, that 20 and 25 are two publisher readings. |
| F-06 | GP-2 (primary), UX-4 | Gerhardt-Powals | `/database/races`, "What a race pays", populated, both themes | The table (`Races.vue:373-409`) prints G1 +10 / +45, G2·G3 +8 / +35, OP·Pre-OP +5 / +30 and the Trackblazer SP line. Every figure matches `docs/UMAMUSUME_REFERENCE.md:156` exactly, and the note is honest about the gap ("Second and third are not published"). What is missing is the rule's owner: the reference marks this same table "a derivation, and its provenance is mixed… the figure is a publisher's arithmetic, not a decoded client value, and… a tracker must print the payout the client shows rather than recompute it", and cites Game8 572831 tier A fetched 2026-10-15. The races page carries no `SourceNote`, no tier, no read date and no ledger link anywhere (`grep -c SourceNote` → Races 0, Events 2, ShopItems 3, Sparks 2). The same page does mark the 1400 m dispute in full, so the instrument is present and simply not applied here. `GP-2` is the narrowest rule: a derived figure states the rule that produced it and whether the rule is the client's or ours. | OBSERVED_RENDERED + OBSERVED_SOURCE | `resources/js/pages/Database/Races.vue:373-409`; `docs/UMAMUSUME_REFERENCE.md:156`; `components/database/SourceNote.vue:26-41` | 2 | High | Add `SourceNote` under the two typed reference cards with `section`, `anchor` and the ledger link, matching `/database/sparks`. Effort S. Acceptance: the payout card is followed by "Race payouts… Read on 2026-10-15. Where this dataset comes from". |
| F-07 | UI-1 (primary), H4; DESIGN.md §typography four-tier table | Laws of UI | every in-scope surface, populated, both themes | Distinct computed font sizes per surface, measured at a settled fresh load: `/database` 9 (11, 11.5, 12, 12.5, 13, 13.5, 14, 16, 28); `/umamusume` 9; `/database/races` 11 (adds 10, 15); `/database/events` 11; `/database/sources` 11; `/database/shop-items` 12; `/database/sparks` 12; `/database/scenarios` 13; `/support-cards/187` 10. `DESIGN.md:346-360` (owner-signed amendment, 2026-10-14) fixes four tiers — Critical `text-3xl`, Heading `text-lg`, Body `text-sm`, Metadata `text-xs` — and `UI-1` permits three. The surplus sizes are hardcoded arbitrary values, not tokens: `text-[28px]` and `text-[13px]` at `components/ui/PageHeader.vue:67,70`, `text-[12.5px]` at `layouts/AppLayout.vue:502`, `text-[11.5px]` at `components/ui/Pill.vue:19`, `text-[10px]`/`text-[11px]` at `components/ui/Tile.vue:31,39`, and `npm run lint:ui` reports 215 arbitrary-value findings (exit 0 under the 219 baseline, i.e. this is enumerated deferred debt, not a new value). Filed once as systemic, not eleven times per page. | OBSERVED_RENDERED + OBSERVED_SOURCE | measured size sets above; `DESIGN.md:346-360`; `tools/ui-lint-baseline.json` `count: 219`; the five component lines | 2 | High | Fold the surviving steps into the four tiers or name the extra tiers in `DESIGN.md`; each deletion of a `text-[Npx]` retires one of the 219 enumerated values. Effort M. Acceptance: ≤ 5 distinct computed sizes on a Database page. |
| F-08 | TOG-3 (primary), H2, WCAG 2.4.6 | Tognazzini | `/umamusume` and `/database/trainees`, populated | `GET /database/trainees?search=Form%20Counted` returns `Showing 1–4 of 4 trainees` with live detail URLs `/umamusume/form-counted-1` … `-4`; all four sit inside the default browse too, because every one of the 71 `umamusume` rows carries `release_status = GlobalReleased` and the door applies no source filter. These are not Umamusume. The repository already knows: `DashboardController.php:93-98` records "the dev database carries four test-written trainees ("Form Counted 1" to "4") that no pipeline ever sourced", and the hub's `external_ref is not null` clause is the stated answer to 67 against 71. The screens do not say so. `docs/UMAMUSUME_REFERENCE.md` §5 is the roster authority and no such name appears in it. | OBSERVED_RENDERED + OBSERVED_SOURCE | live search result and URLs; `umamusume` rows 68–71 with `external_ref NULL`, `is_manual 0`; `app/Http/Controllers/DashboardController.php:93-98`; `tests/Feature/CatalogRosterTreeTest.php:212` | 2 | High | Apply the same join rule the hub uses (`external_ref is not null`) to the catalog read path, or mark such rows, so a Trainer cannot open a detail page for a test fixture. Effort S. Acceptance: the search for `Form Counted` returns no rows on a populated dev database. Also worth a `KNOWN-ISSUES.md` entry; it is currently recorded only in a controller comment. |
| F-09 | WCAG-2 / SC 1.4.3 (primary), H4; DESIGN.md:303 | WCAG 2.2 | `/support-cards/{id}`, limit-break row, populated, **light**, 320 and desktop | Four consecutive `<span class="font-mono text-ink-faint">◇</span>` markers render at `rgb(140, 138, 154)` on `rgb(255, 255, 255)`: measured ratio **3.38:1** at 14px, with no `title` and no `aria-hidden` on the span (so it is exposed as text and announced). `DESIGN.md:303` states the ink step is "non-text only: borders, ticks, disabled glyphs. Measured as body text it fails, which is why `ink-muted` is the subordinate text tier", and `DESIGN.md:657` records the same step at 3.26:1 light. The meaning is also carried in words beside the glyphs ("breaks not recorded", "cap 45"), which is why this is severity 2 and not 3. | OBSERVED_RENDERED + OBSERVED_SOURCE | `resources/js/pages/SupportCards/Show.vue:324-329`; `DESIGN.md:303`, `:657`; `resources/css/app.css:152` (`--color-ink-faint: #8C8A9A`) | 2 | High | Use `text-ink-muted` for the unfilled diamonds, or mark the run `aria-hidden="true"` and let the adjacent words carry it. Effort S. Acceptance: the marker clears 4.5:1 in light, or is absent from the accessibility tree. |
| F-10 | H4 (primary), SHR-1, WCAG 2.4.6 | Heuristics | page title, every in-scope surface vs the error documents | The Critical tier ships twice. `PageHeader.vue:67`, `AppLayout.vue:577` and `SetupLayout.vue:216` all render `text-[28px] font-extrabold`, measured at **28px / weight 800**; `resources/views/errors/404.blade.php:44` renders `text-3xl font-semibold`, measured on the live 404 at **30px / weight 600**. `DESIGN.md:346-353` (owner-signed) names the tier `text-3xl font-semibold` and its amendment reasons explicitly about the 24px-to-30px gap. Per `AGENTS.md` §2 the code wins and the rule is stale, so this is filed as the **shell-versus-error split** the user actually sees — the same page role in the same product renders two sizes and two weights — and the `DESIGN.md` amendment is named as the stale text rather than treated as the defect. | OBSERVED_RENDERED + OBSERVED_SOURCE | measured `getComputedStyle` values on `/support-cards/187` (28px) and `/database/nope` (30px); the three shell lines; `DESIGN.md:346-353` | 2 | High | Pick one Critical treatment and ship it from one place; the error documents are the only surface outside the shell today. Effort S. Acceptance: the h1 of a Database page and of a 404 compute the same size and weight. Say in the report, not in the file, that `DESIGN.md` needs a dated erratum, which is the Docs Writer's to land. |
| F-11 | UX-1 (house 44px), H6 | Laws of UX | breadcrumb, every Database page, populated, desktop | The crumb's own link is measured at **51×16px** (`A:Database` inside `span.min-w-0.text-[12.5px]`, text `Database · Races`), against a house rule of 44. It is an inline link in a text line, so WCAG 2.5.8's inline exception is available and this is **not** filed as a conformance failure; every other target on the area pages clears the house rule (rail links 47×44 with 45px centre gaps, subnav items 44px tall on every page, filter controls `min-h-11`, the `Clear` link 47×44). Filed because the crumb is the only escape path that is not a 44px target. | OBSERVED_RENDERED | measured boxes on `/database/races`, `/database/sparks`, `/umamusume`, `/support-cards/187` | 1 | High | Give the crumb link `min-h-11` like the subnav items, or accept it as a named exception in `DESIGN.md` under SHR-1. Effort S. |
| F-13 | WCAG SC 1.4.10 (primary), H4, SHR-1 | WCAG 2.2 AA | `/support-cards`, populated, light **and** palette-independent geometry, 320×640 and 390×844 | The document itself scrolls sideways: `documentElement.scrollWidth - clientWidth` = **248 at 320** and **178 at 390**, and `scrollingElement.scrollLeft` reaches those same values, so it is the page scrolling in two dimensions, not a component. The sole cause is one visually-hidden label: `<span class="sr-only">Open the card</span>` in the last header cell (`SupportCards/Index.vue:285`), which is `position:absolute` inside `div.card.mt-4.overflow-x-auto` (`:275`) whose own `position` is `static` — so the label's containing block sits above the scroll container and its 1px box extends the document instead of being clipped. Hiding that single element returns the overflow to exactly 0 at both widths, twice over. At 200% root font-size the page overflows 612px (557 of it that label, the residual 55 an in-flow table with no scroll wrapper), and with the SC 1.4.12 text-spacing override 241px — one cause, three symptoms. The twin that renders the same rows through `components/ui/DataTable.vue` (`/database/supports`) measures 0 overflow at 320/390/768/desktop, and eleven of the twelve other surfaces measured 0 at 320. | OBSERVED_RENDERED | `resources/js/pages/SupportCards/Index.vue:275,285`; live deltas 248/178/612/241 with the hide-one-element control | 2 | High | Make the scroller the containing block: add `relative` to the `overflow-x-auto` card at `:275` (one token, no markup change), or retire this hand-rolled table onto `DataTable.vue`, which already behaves. Acceptance: at 320 with the default and with `font-size:200%` on the root, `documentElement.scrollWidth == documentElement.clientWidth` on `/support-cards`, and `scrollLeft` stays 0. |
| F-12 | IA-1 (primary), H2 | Information architecture | `/database` hub, Events tile, populated | The tile reads `Events \| Reference \| Support-card and trainee event chains.` The door it opens renders ten recurring **live-event** rows (Champions Meeting, Story event, Legend Race, Special event (Spark and item research), Masters Challenge, Season pass, …) from `config/reference.php:50-140`, and its own `SourceNote` says "Recurring event types". Not one card or trainee event chain is listed on the page, although the reference document counts them (`docs/UMAMUSUME_REFERENCE.md:521`: 135 per-character chains, 11 Pal chains, 5 group chains) and the data behind them exists on the card pages. A Trainer who wants the chains is sent to a page that does not hold them, by a sentence that says it does. | OBSERVED_RENDERED + OBSERVED_SOURCE | live hub tile text vs live `/database/events` table; `config/reference.php:50-140`; `app/Http/Controllers/DatabaseController.php:73`; `docs/UMAMUSUME_REFERENCE.md:521` | 1 | High | Reword the tile description to what the area holds ("Recurring live-event types and their cadence"). Effort S, copy only. Acceptance: the tile names the table it opens. |

## 6. Unmeasured register

| Claim | Why unmeasured | What would settle it |
|---|---|---|
| Page-level dark and `eva01` paint on any surface | Forcing `data-theme` on a live session does not converge (§4: the painted surface lags the token by one step, in both directions), so no per-page dark figure from this audit is admissible — including pass 2's | Set the theme through `/preferences` on a session-private database, then re-walk; `G-18` already proves the token pairs in all three themes (357 assertions, exit 0) |
| 400% zoom, and 200% text on the twelve surfaces other than `/support-cards` | The resize pass ran only on the one surface that already failed at 320; 400% was not attempted | Repeat the root-`font-size` and `scrollWidth`-versus-`scrollLeft` probe per surface at 200% and a zoom pass at 400% |
| Whether the residual 55px at 200% on `/support-cards` is the same defect or a second one | Hiding the header label left 55px of document overflow from an in-flow table, which was not localised | After applying the F-13 fix, re-measure at 200%; if 55px remains, bisect the tables on that page |
| Full keyboard traversal, and SC 2.4.11 beyond two surfaces | Tab was walked on `/umamusume/admire-vega` and `/database/races` only; no sticky overlap was found on either | Tab the whole tab order of the three dense tables and compare each focused rect against the sticky rail |
| TOG-1 acknowledgement within 50ms; the `prefetch="hover"` behaviour on hub tiles | `VisitProgressBar` exists (`AppLayout.vue:347`) and timing was not measured | Frame-level timing on a tile click |
| Loading state as a rendered state | Source-level it is N/A: these are server-rendered pages and `AGENTS.md` §13 bans decorative skeletons, but that was read, not seen | Throttle a request to `/database/races` and observe the shell during the visit |
| Whether the `◇`/`i` glyph markers are "text" under SC 1.4.3 or "non-text" under 1.4.11 | Measured 3.38:1 and 3.89:1; both clear 3:1, neither clears 4.5:1 | One line in `DESIGN.md` saying which way a status glyph reads; then F-09 either firms up or closes |
| Table naming for screen readers | 2 of 3 tables on `/database/races`, 3 of 11 on `/database/scenarios`, 4 of 6 on `/database/sparks` and 3 of 3 on `/database/sources` carry no `<caption>` and no `aria-labelledby`, though each sits under a visible `h2`/`h3` inside the same `.card`, and every `<th>` carries `scope` | No success criterion requires a caption, so this is recorded as an observation, not a finding; `1.3.1` is met. Settled by a screen-reader read-through, or by wiring `aria-labelledby` to the heading |
| Whether the four `Form Counted` rows should be purged from the dev database or filtered at read | Owner call; F-08 files the read-path fix only | Owner ruling, then either a cleanup command or the join filter |

## 7. Conflict and gap log (not findings)
- Settlements applied rather than filed: **44px vs SC 2.5.8** — F-11 is a house-rule gap, not a
  conformance failure, and the crumb's inline exception is named. **Density** — the Database area is the
  recorded exemption (`DESIGN.md`/`SCREEN_SPEC` SCR-SYS-006…010), so 25-row tables and 12px metadata are
  not findings. **Closure vs honesty** — partial indicators here carry words ("Not recorded", "N/A" with a
  reason, "breaks not recorded", "no winner is encoded"), so GT-2/UI-4 hold; no finding.
  **Consistency vs exceptions** — the dark ramp's named deviations were not re-litigated.
- Framework-set discrepancy, recorded so no future pass invents it: **`SHR-8` does not exist.**
  `shneiderman-golden-rules-2026-10-21.md:135-141` ends at `SHR-7` ("The value a decision needs is on the
  decision screen", mapped to Shneiderman rule 8). The operating prompt's §10 index lists `SHR-8` as that
  rule, and pass 1's coverage matrix cited `SHR-8`. Per Phase 6 gate 1 (a rule must exist in the file),
  this pass cites `SHR-7` and files nothing under `SHR-8`. Same caution applied to `UI-8`: `uilaws` defines
  `UI-1`…`UI-7`.
- Instrument corrections, kept because each one killed a candidate. **Pass 3's is the largest: the
  forced-`data-theme` method does not converge, so every page-level dark-theme number in this report
  (in both passes) is withdrawn** and replaced by the `G-18` token-pair gate; see §4. Before that
  discovery, mid-transition sampling was blamed — `.card` carries `transition-colors duration-150`
  (`resources/js/pages/Database/Index.vue:25`), so a flip read too early returns an animating surface
  under an already-flipped colour. Also: the dimmed `Previous` control measured 1.03:1 but carries
  `disabled`, so SC 1.4.3's inactive exception applies and it is not a finding; the hub tiles'
  absolute `http://127.0.0.1:8000/…` hrefs were checked for a forced full-document reload and survive
  as SPA visits (`window` state lived through the click), so no finding; and `Races.vue:79-81`'s
  `Maiden` and `Debut` grade options were probed for a dead filter and are backed by real rows
  (`tier = 'Maiden'` 1, `tier = 'Debut'` 1). A fourth near-miss was avoided by measuring the document
  rather than the containers: `/support-cards` looked clipped at every width until `scrollLeft` was
  actually moved (F-13).
- Content verifications that **passed** and are recorded here so they are not re-audited from scratch:
  the four scenario cap rows match `docs/UMAMUSUME_REFERENCE.md:418` exactly, as do `base_cap` 1200
  (`:417`), `hard_cap` 2000 (`:422`), the `cap_bonus` arithmetic at `:906-919`, and the four
  `live_on_global` dates against `:741-745`; the hub's `Scenarios 4` matches the document's "[Global]
  has 4 total selectable" (`:722`); the blue-spark `+5/+12/+21` ladder (`:655`), the `+16` ★3 cap raise
  (`:420`) and the doubled "per two parents" column; the three star-roll odds bands (`:1.5.3`),
  including the "roughly one-in-ten roll at three stars, not a three-star Spark" caution; the pink
  aptitude ladder `★1·4·7·10-18 → +1/+2/+3/+4`; the SR level cap 45 with four breaks at +5 and
  "a fully broken card is five copies" (`:501,503,505`); the hint ladder 10/20/30/35/40 (`:2569`);
  the 500/550 star-breakthrough totals (`:435`); the 623 Global skill count as the scoping rule;
  `Support Cards 559` matching the document's own "559 cards" (`:438`); the 410-slot race table
  labelled "slots" on its own page; the three `Open conflicts` entries on `/database/sources` each
  matching a real conflict-log row (7 `:259/:2560`, 2 `:2555`, 10 `:2563`); both dated row-count errata
  for these views still printed (`SCR-SYS-009` four-of-ten match at `ShopItems`, `SCR-SYS-010`
  806-described/874-export/68-remainder at `Sparks`); the 1400 m conflict marked per row, in a footnote
  and in an `aria-label` (`Races.vue:296-301,314`); and the four `Not recorded`/`N/A` absence channels,
  none of which defaults a value.
- Gaps (register, not per-screen findings): **H3** undo — N/A on read-only surfaces; **H7** no
  accelerators anywhere in this area; **H10** no reactive help surface on these pages (the shell's help
  path is outside this scope). All three are already in the heuristics file's gap register and are
  confirmed, not re-filed.

## 8. Recommendations
1. **F-13** — add `relative` to the `overflow-x-auto` card at `SupportCards/Index.vue:275` so it
   becomes the containing block for the `sr-only` header label, or retire this page's hand-rolled
   table onto `components/ui/DataTable.vue` (which `/database/supports` already uses and which does not
   overflow). Satisfies SC 1.4.10. Effort S, one class or one component swap. Acceptance: at 320 and
   at 200% root font-size, `documentElement.scrollWidth == clientWidth` on `/support-cards`.
2. **F-04** — read each source's own newest `data_sources.fetched_at`; print `No fetch recorded` where a
   source has none. Satisfies TOG-3/UX-4. Effort S (one grouped query). Acceptance: two rows differ and
   `gametora-artwork` shows no date.
3. **F-01 + F-02** — build and land the two edits already in the working tree rather than re-doing them
   (`Index.vue:88`, `Scenarios.vue:191-198`). Satisfies UX-4/SHR-7. Effort S. Acceptance: tiles print
   `Fetched`/`Transcribed`/`Config`, and the scenarios note links the ledger. Check `git diff HEAD`
   first: both files are dirty from another session, and `npm run build` is a write (§9, KI-70).
4. **F-03** — make each counted hub tile's figure reachable from its own door, following
   `/database/supports`'s existing dual-count pattern. Satisfies UX-9. Effort S-M.
5. **F-08** — filter or mark the four test-sourced trainee rows on the read path. Satisfies TOG-3.
   Effort S. Acceptance: `?search=Form Counted` returns nothing.
6. **F-06** — attach `SourceNote` to the two typed reference cards on `/database/races`. Satisfies
   GP-2/UX-4. Effort S.
7. **F-05** — surface the Artisan hammer price as an open conflict, in the register that already exists.
   Satisfies GP-2. Effort S. The doc's own 20-versus-25 disagreement needs a ruling; that is the owner's
   to write.
8. **F-09** — move the limit-break markers to `text-ink-muted`, or hide them from the accessibility
   tree. Satisfies SC 1.4.3/DESIGN.md:303. Effort S.
9. **F-10** — unify the Critical tier between shell and error documents, then hand `DESIGN.md` a dated
   erratum. Satisfies H4. Effort S.
10. **F-07** — retire the arbitrary type steps toward the four tiers. Satisfies UI-1. Effort M, and each
    removal retires one of the 219 enumerated baseline values.
11. **F-11, F-12** — copy and sizing one-liners. Effort S each.
12. **Method, for the next pass** — do not measure a theme by writing `data-theme` onto a live page.
    Set it through the app on a session-private database, or trust `G-18` for token pairs; a settle
    delay is not enough (see §4).

## 9. Change-audit re-check (M1 only)
N/A. This is M4. Nothing in the tree was changed by this audit; it is read-only.

## 10. Exit checklist
- [x] Scope, mode, surfaces, states, viewports and themes stated, including what was not covered
      (page-level dark/`eva01` paint, 400% zoom, full keyboard traversal) — §3 and §6.
- [x] Framework routing recorded; narrowest rule chosen per question; content findings labelled as the
      owner-ordered track.
- [x] Pre-flight provenance caveats carried (§4), including the 9-vs-10 Gerhardt-Powals split.
- [x] Evidence capability recorded: RENDERED_EVIDENCE TRUE, **with the theme-forcing instrument
  limitation named and the affected numbers withdrawn rather than left standing** (§4, §7).
- [x] Every finding has a rule ID, a surface and state, a specific observation, an evidence status and
      a `file:line` verified this run.
- [x] No severity above 2 was assigned; nothing at severity 3 or 4 was asserted, and the candidates
      that looked like severity 4 were disproved (§7).
- [x] Severity and confidence kept on separate axes.
- [x] No defect filed twice; F-07 is filed once for the whole surface set, F-01/F-02 kept separate
      because they are different surfaces and different rules.
- [x] Known conflicts logged as settlements, not findings (§7).
- [x] Gaps kept in the gap register (§7).
- [x] Unmeasured register names what would settle each claim (§6).
- [x] Report follows the §7 template of the operating prompt.
- [x] No WCAG conformance claimed beyond the criteria actually tested; 4.1.1 never cited; the 44px rule
      never described as 2.5.8.
- [x] Contents cross-checked against `docs/UMAMUSUME_REFERENCE.md`: six discrepancies filed (F-03,
      F-04, F-05, F-06, F-08, F-12), and the verified-accurate set recorded so it is not
      re-derived from scratch.
- [x] No code, no data, no other document modified. Pass 3's measurements included temporary in-page
      mutations (hiding the F-13 label, a root `font-size` override, a temporary SC 1.4.12 style
      element, a forced `data-theme`) — all reverted, none persisted; `G-18` was run read-only and its
      log and probe scripts live only under `.scratch-uma/aud-database-2026-11-02/`.

## 11. Pass-1 record (2026-11-01), preserved as written
This report file, and its three siblings `AUD-career-surfaces-2026-11-01.md`,
`AUD-dashboard-2026-11-01.md` and `AUD-preferences-2026-11-01.md`, are **untracked**
(`git status --short docs/audits/` lists all four as `??`). Pass 1 therefore exists nowhere in history,
and the only record of it is the section below. The `HEAD` comparisons in this report are made against
the application code, which is tracked; nothing here claims a committed version of the audit file.
Pass 1 ran as AUD-20261101-004 with `RENDERED_EVIDENCE = FALSE` (`spawn EPERM`) and filed two
OBSERVED_SOURCE findings, both severity 2, both High confidence:
- F-01: the hub computed `source` per area and never rendered it. **Status 2026-11-02: still true at
  `HEAD`, still true on the live page, and already fixed by an uncommitted one-line edit in the working
  tree that has not been built or landed.** Carried forward as F-01 here.
- F-02: the Scenarios verification note named its sources as plain text with no link to the ledger.
  **Status 2026-11-02: unchanged; a peer's uncommitted `<Link>` edit is in the tree, unbuilt.**
  Carried forward as F-02 here.
Pass 1 also dropped two earlier candidates as false positives (the race-search `_` silent non-match,
and "no path from area pages to `/database/sources`"), and pass 2 re-checked both drops and agrees
with them: the controller escapes `%` and `_`, a no-match hint and a `Clear` link are live, and
`DatabaseNav` reaches the ledger from every area page.
