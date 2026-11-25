# 02 — Screen-by-screen layout resolutions

Primary deliverable of the bento design-language audit. One section per inspected user-facing screen (66 screens), each with: current layout classification, design compliance, source authority, current structure, what works, what is inconsistent, recommended resolution, layout sketch, implementation location, priority, decision status, and acceptance criteria.

Read-only audit: no application code was modified.

---

## Area: Career — Dashboard & shell

### Screen: SCR-CAR-001 — Dashboard (`/`, `home`)

**Current layout classification:** Bento-primary

**Design compliance:** Compliant (with P2 refinements)

**Source authority:** DESIGN.md §2.3 (bento shell widths `g-side`/`g-4`, `.card`, `.tile`), SCREEN_SPEC §4.21 (Dashboard structure: five panels in order), SCREEN_SPEC §2 (nav shell), HFW visual reference.

**Current structure:** Page head (`PageHeader eyebrow="Dashboard" title="Trainer Desk"` with secondary actions "New career" + "All careers"; the one filled action "Resume Career" lives inside the active-career card). Row 1: `g-side` grid — "Pick up where you left off" (≈2/3) beside "Needs you" (≈1/3). Row 2: `g-4` KPI strip — four aligned tiles. Row 3: "Recent Veterans" beside "Quick actions". Row 4: "Data status" panel. Empty states for no active career / no Veterans / no turn logged; page-level `role="status"` loading and `role="alert"` error.

**What works:** Canonical bento composition — primary:supporting split via `g-side` (2.1fr/1fr), aligned KPI tiles via `g-4`, consistent `.card` surfaces, one filled action per screen, `N/A` with reason for absences, all three states (empty/loading/error) present. This is the visual reference for the whole audit.

**What is inconsistent (P2):** Empty-state wrapper uses bare `class="card"` instead of the Section primitive used elsewhere; footer copy "7 veterans · 3 shown" sits inside the grid rather than as a caption; the KPI strip header row duplicates count language.

**Recommended resolution:** Keep the five-panel bento structure unchanged. Replace the bare `.card` empty wrapper with `Section` (or align its padding to `.card` tokens). Move the "7 veterans · 3 shown" line below the grid as metadata caption. No panel reordering or resizing — the Dashboard is the compliant reference.

**Layout sketch:**
```
Desktop (g-side / g-4):
Row 1: [Active career ≈2/3] [Needs you ≈1/3]
Row 2: [KPI tile][KPI tile][KPI tile][KPI tile]   (g-4, aligned)
Row 3: [Recent Veterans ≈2/3] [Quick actions ≈1/3]
Row 4: [Data status, full width]
Tablet (≤845px canvas): g-side → 1 column, panels stack in reading order
Mobile (compact): all grids 1 column, actions wrap under content
```

**Implementation location:** `resources/js/pages/Dashboard.vue`; shell `resources/js/layouts/AppLayout.vue`; grid `resources/css/layout-grid.css`.

**Priority:** P2 (refinements only; structure is compliant)

**Decision status:** Authorized recommendation (no owner gating; structure matches HFW and DESIGN.md)

**Acceptance criteria:** Five-panel bento retained; empty wrapper uses Section primitive or `.card`-aligned padding; "7 veterans · 3 shown" caption below grid; KPI tiles remain aligned via `g-4`; one filled action per screen unchanged.

---

### Screen: Global navigation shell (`AppLayout.vue`)

**Current layout classification:** Conventional (shell chrome, not a content screen)

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §2 (nav shell: ten destinations, four `inBar` on mobile, More disclosure), DESIGN.md §2.3 (shell widths, rail geometry), `resources/js/layouts/AppLayout.vue`.

**Current structure:** Sticky rail (236px / 56px collapsed), brand tile, group headings, ten destinations in four groups, foot with "Data sources" + version line. Topbar: brand-chrome fill + brand-accent hairline, crumb, `#who` pill slot, Help link. Page head fallback band. Canvas `@container` on `main`. Mobile bottom bar: 5 slots + More `<details>`.

**What works:** Grouped rail satisfies "at or under eight" via grouping without deleting destinations; mobile bar does not attempt to shrink the desktop sidebar onto mobile (HFW §41); skip link; focus-trapped More disclosure; one `#who` pill only; `position: sticky` no-op documented (load-bearing `@container`).

**What is inconsistent:** None material. The rail's 10 destinations exceed plan §13's "at or under eight" target, but this is a recorded constraint breach (SCREEN_SPEC §2 correction) owned as a grouping decision, not a layout defect.

**Recommended resolution:** No change required. The shell is the compliant container for all bento screens.

**Priority:** P3 (none — compliant)

**Decision status:** No change required

**Acceptance criteria:** Shell remains the single renderer for all AppLayout screens; rail/mobile split unchanged; `@container` preserved.

---

### Screen: SCR-SYS-Global nav (mobile bottom bar)

Covered under Global navigation shell above.

---

## Area: Career — Wizard (SetupLayout)

### Screen: SCR-CAR-002 — Scenario Selection (`/career/setup/scenario`, step 1)

**Current layout classification:** Conventional (wizard step, sequential single-column)

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.22, DESIGN.md §2.3 (wizard shell widths, compact mode), `resources/js/layouts/SetupLayout.vue`.

**Current structure:** SetupLayout step bar (step 1 of 6), stored-choice line, one card per scenario in matrix order. Each card: name, optimization focus, systems, tracked resources, turn loop, Global availability date, ruleset date, ruleset version, description slot, recommended-use slot, one `Select Scenario` action (`aria-pressed`). Facts grid is a plain container (not `role="list"` — axe fix recorded). Empty: `N/A` with title when no draft choice.

**What works:** Sequential step flow is correct for a choice-with-consequences wizard; one primary action per card; facts grid fixed for `aria-required-children`; no pre-selection.

**What is inconsistent:** None material. Description and recommended use are named absences (`N/A` with reason) — not a layout defect.

**Recommended resolution:** No change required. Wizard steps are conventional by necessity — a sequential single column keeps the choice committed before advancing.

**Layout sketch:**
```
SetupLayout shell:
[Step bar: 1 of 6]  [stored-choice line]
[Scenario card 1][Scenario card 2]...   (g-2 → 1 col compact)
[Select Scenario] per card
```

**Implementation location:** `resources/js/pages/Career/ScenarioSelect.vue`; shell `resources/js/layouts/SetupLayout.vue`.

**Priority:** P3 (no change)

**Decision status:** No change required

**Acceptance criteria:** Single column retained; one `aria-pressed` action per card; facts grid keeps absent `role`; empty states name reasons.

---

### Screen: SCR-CAR-003 — Trainee Selection (`/career/setup/trainee`, step 2)

**Current layout classification:** Conventional (wizard step, sequential)

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.23, DESIGN.md §2.3.

**Current structure:** SetupLayout step 2 of 6, scenario line (`N/A` with title when unset), stored-choice line, filter form (search + Surface/Distance/Style/unique-skill facets + sort + Filter/Clear), one card per trainee (debut form first, then Global release date, then card id). Card: name + name_ja, release status, `size-10` ArtworkSlot (reserve, decorative), ten AptitudeBadge letters, per-form RarityChip, primary "Select Trainee" (`aria-pressed`), "View Profile" link. Forward door disabled with reason until a trainee holds. Empty: "no trainee matches" with path out (seed or `uma:fetch`, Clear filters).

**What works:** Filter row is a single inline form row; roster cards use consistent `.g-3` grid; aptitude badges use letter+word (D-12, never colour alone); empty state names remedy.

**What is inconsistent:** Growth-rate and scenario-suitability facets are omitted (no column holds them) — recorded as a gap, not a layout defect. No layout inconsistency.

**Recommended resolution:** No change required. Conventional single-column wizard step is appropriate; filter row stays inline above results.

**Priority:** P3 (no change)

**Decision status:** No change required

**Acceptance criteria:** Filter row remains single inline form row; roster grid stays `.g-3`; aptitude badges letter+word; empty states name remedies.

---

### Screen: SCR-CAR-004 — Trainee Profile (`/career/setup/trainee/{umamusume}`)

**Current layout classification:** Conventional (detail/read screen)

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.24, DESIGN.md §4.2 (catalog detail ported structure).

**Current structure:** PageHeader (eyebrow, h1 trainee name + name_ja, lede). Eight sections in order: Identity, Aptitudes, Skills, Costume forms, Goal races, Her runs, Aliases, Provenance. Absence: `N/A` with title (never Unknown/dash/zero). Primary action "New run for <name>".

**What works:** Sectioned detail page with clear hierarchy; provenance last and quiet; `N/A` discipline; Primary action well-placed.

**What is inconsistent:** None material. Sections 3-5 have no stored data on this reference and say "not yet recorded" — that is the honest absence pattern.

**Recommended resolution:** No change required. Conventional detail layout is correct for a reference workspace.

**Priority:** P3 (no change)

**Decision status:** No change required

**Acceptance criteria:** Eight sections in documented order; `N/A` with titled reason; primary action "New run for <name>" present.

---

### Screen: SCR-CAR-005 — Build Target (`/career/setup/target`, step 3)

**Current layout classification:** Conventional (wizard step, sequential form)

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.25, DESIGN.md §2.3.

**Current structure:** SetupLayout step 3 of 6. Form card: trainee combobox, scenario select, status select, inheritance A/B selects, Grade Point period number, Shop resets number, notes textarea, submit. `max-w-lg` narrow form. Combobox is the control (dual native+combobox fallback retired per ADR-0020 §1).

**What works:** Single-column form; selects over free text where enums exist; `max-w-lg` narrow form per ratified width.

**What is inconsistent:** Fifth purpose (Competitive Build), risk tolerance, per-skill marks not recorded — stated on the page as omissions, not a layout defect.

**Recommended resolution:** No change required. Conventional form layout is correct.

**Priority:** P3 (no change)

**Decision status:** No change required

**Acceptance criteria:** Single-column form retained; `max-w-lg` width; one submit action.

---

### Screen: SCR-CAR-008 — Legacy Select (`/career/setup/legacy`, step 4)

**Current layout classification:** Conventional (wizard step, sequential)

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.25 (SCR-CAR-008 row), DESIGN.md §2.3.

**Current structure:** SetupLayout step 4 of 6. Two parent cards beside recorded Sparks. Six-node graph moved to run-scoped InheritanceEvent (per SCREEN_SPEC dated note). "What inheritance actually does" table corrected against UMAMUSUME_REFERENCE §1.5.2.

**What works:** Two-parent card layout beside Sparks; factor table read-only; legacy terminology (Inspiration/Legacies/Sparks) per §6 lore rules.

**What is inconsistent:** None material.

**Recommended resolution:** No change required.

**Priority:** P3 (no change)

**Decision status:** No change required

**Acceptance criteria:** Two parent cards + Sparks layout retained; no six-node graph on this step (moved to InheritanceEvent).

---

### Screen: SCR-CAR-009 — Support Deck Select (`/career/setup/deck`, step 5)

**Current layout classification:** Conventional (wizard step, sequential)

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.25 (SCR-CAR-009 row), DESIGN.md §2.3.

**Current structure:** SetupLayout step 5 of 6. Six slots with OWNED/RENTED in draft, seven types, D6 analysis. `DeckSummary.vue` mounted between `g-side` grid and duplicate rule, printing position/card/type/rarity/OWNED-RENTED/level/break tier beside per-card value ladder (0 breaks → Max Limit Break).

**What works:** Deck summary placed between grid and duplicate rule; value ladder uses `CardRarity::levelAtBreak()` and `SupportCardEffects::atLevel()`; no break count stored (ADR-0014 decline).

**What is inconsistent:** None material.

**Recommended resolution:** No change required.

**Priority:** P3 (no change)

**Decision status:** No change required

**Acceptance criteria:** Six slots with OWNED/RENTED; DeckSummary between grid and duplicate rule; no PUT on level change (spec-pinned).

---

### Screen: SCR-CAR-010 — Preflight / Review and start (`/career/setup/preflight`, step 6)

**Current layout classification:** Conventional (wizard step, sequential review)

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.25 (SCR-CAR-010 row), DESIGN.md §2.3.

**Current structure:** SetupLayout step 6 of 6. The wizard's one write — composes the five entered steps and creates the run. Review of entered choices before commit.

**What works:** Final commit step; single primary action; errors inline per field.

**What is inconsistent:** None material.

**Recommended resolution:** No change required.

**Priority:** P3 (no change)

**Decision status:** No change required

**Acceptance criteria:** Single commit action; per-field errors; run created once at Preflight.

---

## Area: Career — Run-scoped decision/detail screens

### Screen: SCR-CAR-011 — Career Cockpit (`/training-runs/{run}/cockpit`, `runs.cockpit`)

**Current layout classification:** Bento-primary

**Design compliance:** Minor inconsistencies (P1/P2 mix)

**Source authority:** SCREEN_SPEC §4.26 (SCR-CAR-011 row), DESIGN.md §2.3, HFW override register §8.25 (Cockpit rows).

**Current structure:** PageHeader (eyebrow "Cockpit", h1 trainee, lede with import, actions: "All careers" secondary + primary head action). Run-state strip: empty state ("No turn recorded yet" Section above grid), closed-run outcome section (finale/turn/races/skill points tiles), N3.2 career-status strip (last recorded turn / next turn to play). Advisor rail with recommendation block. Scenario region (ScenarioPanel — now mounted behind its own door at SCR-CAR-029 per v3.0.0 surgery). `Career tools` disclosure below HFW's last region (run identity form, turn correction, delete-turn, delete-run).

**What works:** PageHeader adopted (one h1); one primary head action; advisor recommendation single per screen; tiles use `.tile` primitive; status strip uses `.tiles grid-cols-2`.

**What is inconsistent (P1):** `Career tools` disclosure sits below HFW's last region holding writes with no dedicated doors — recorded HFW override, filed as KI-144, Slice 6b to retire once doors exist. This is a structural debt, not a visual defect.

**What is inconsistent (P2):** Browser specs fail on `Races in this run` region (gated on `isActive`) and `Actions` heading (inside disclosure closed on load) — assertion/gate mismatch, recorded in HFW override register, not a layout defect to "fix" by changing the UI.

**Recommended resolution:** Keep bento-primary composition (header strip + advisor rail + scenario region + panels). Retain `Career tools` disclosure as interim home for writes (owner-gated: do not delete before doors exist — data-loss risk). No panel resizing; the Cockpit's bento hierarchy is the correct pattern for a run-scoped primary screen.

**Layout sketch:**
```
Desktop:
[PageHeader: h1 trainee | lede | All careers | primary action]
[Career status strip: last turn | next turn]   (.tiles grid-cols-2)
[Advisor rail: recommendation | obligations]
[Scenario region: ScenarioPanel (door to SCR-CAR-029)]
[Career tools disclosure: identity form | turn correction | delete actions]
Tablet: advisor rail stacks under status strip; scenario region full width
Mobile: single column, disclosure at bottom
```

**Implementation location:** `resources/js/pages/Career/Cockpit.vue`; `resources/js/components/career/RecordedActionCard.vue`; `resources/js/components/career/NextDecision.vue`.

**Priority:** P1 (Career tools disclosure — owner-gated, do not remove without doors); P2 (spec/gate mismatch — verification only)

**Decision status:** Owner-gated (KI-144, Slice 6b — retire disclosure once write doors exist)

**Acceptance criteria:** Bento structure retained; one primary action; disclosure retained until `runs.update`/`runs.turns.update`/`runs.turns.destroy` doors exist; no panel added/removed.

---

### Screen: SCR-CAR-012 — Training Decision detail (`/training-runs/{run}/training`, `runs.training`)

**Current layout classification:** Bento-hybrid

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.26 (SCR-CAR-012 row), DESIGN.md §2.3.

**Current structure:** PageHeader (eyebrow, h1, lede). Five option cards (one per training stat), per-training yield and failure rate rendered `N/A` with exclusion in `title` (held unsourced). Write posts `choice` as `training-<Stat>`.

**What works:** Five option cards in a grid — clear bento-hybrid with dominant decision region + contextual N/A absences. One filled action per screen.

**What is inconsistent:** None material. Yield/failure rate held as `N/A` — correct for unsourced figures.

**Recommended resolution:** No change required. Option cards are the natural bento-hybrid treatment for a decision screen.

**Priority:** P3 (no change)

**Decision status:** No change required

**Acceptance criteria:** Five option cards retained; `N/A` with titled reason for unsourced figures; one primary submit.

---

### Screen: SCR-CAR-013 — Race Decision (`/training-runs/{run}/races`, `runs.races.decision`)

**Current layout classification:** Bento-hybrid

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.26 (SCR-CAR-013 row), DESIGN.md §2.3.

**Current structure:** PageHeader. Races at the turn being decided. Win figure held on `ADR-0016`, readiness band pending owner ruling — both `N/A`. Four of brief's ten fields named absent with reason.

**What works:** Race list with absent fields explicitly named; no fabricated win probability.

**What is inconsistent:** None material.

**Recommended resolution:** No change required. Conventional decision list with named absences is appropriate; bento not forced.

**Priority:** P3 (no change)

**Decision status:** No change required

**Acceptance criteria:** Absent fields keep titled reasons; no win probability or readiness band until owner ruling (ADR-0016).

---

### Screen: SCR-CAR-014 — Event Decision (`/training-runs/{run}/events`, `runs.events.decision`)

**Current layout classification:** Bento-hybrid

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.26 (SCR-CAR-014 row), DESIGN.md §2.3.

**Current structure:** PageHeader. Record-only: known outcomes derived from run's own recorded choices; advisor refuses with reason; choice without recorded outcome carries incomplete warning. No score or probability computed.

**What works:** Single decision region with advisory refusal; no fabricated scores.

**What is inconsistent:** None material.

**Recommended resolution:** No change required.

**Priority:** P3 (no change)

**Decision status:** No change required

**Acceptance criteria:** Advisor refusal with reason retained; incomplete warning on unrecorded choice; no score/probability.

---

### Screen: SCR-CAR-015 — Inheritance Event (`/training-runs/{run}/inheritance`, `runs.inheritance`)

**Current layout classification:** Bento-hybrid

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.26 (SCR-CAR-015 row), DESIGN.md §2.3 (ancestry/lin-gen/lin-node primitives in layout-grid.css), lore §6 (Inspiration/Legacies/Sparks terminology).

**Current structure:** PageHeader. Predicted section: sourced Spark probabilities and star-roll odds. Observed section: Trainer-entered inspiration outcomes via TurnEvent. Milestone timeline with three fixed events (turns 1, 31, 55). Ancestry spine with parent nodes (`.ancestry`, `.lin-gen`, `.lin-node`).

**What works:** Ancestry spine uses ported primitives; predicted/observed sections separated; milestones fixed; `N/A` for expected inheritance citing ADR-0020 §3.

**What is inconsistent:** None material.

**Recommended resolution:** No change required. Ancestry spine + two sections is the correct bento-hybrid structure for an inheritance event.

**Priority:** P3 (no change)

**Decision status:** No change required

**Acceptance criteria:** Ancestry spine retained; predicted/observed sections; three milestones; `N/A` with ADR-0020 §3 citation.

---

### Screen: SCR-CAR-016 — Skills Planner (`/training-runs/{run}/skills`, `runs.skills.planner`)

**Current layout classification:** Bento-hybrid

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.26 (SCR-CAR-016 row), HFW override register §8.25 (Skills row), DESIGN.md §2.3.

**Current structure:** PageHeader. Four skill states (Suggested/Acquired/Skipped + ?). SP coverage warning. Sourced hint ladder priced per skill. Race-fit cells compare only what a source maps. `.savebar` survives with no primary — status strip (plan count + "Clear plan"); single commit is budget card's "Learn planned skills".

**What works:** `.savebar` as status strip (no primary inside) — HFW override intentional; one commit action; skill states grouped.

**What is inconsistent:** None material. HFW draws no savebar; app keeps `.savebar` class with no second `.btn-primary` — satisfies UX-6 (one accent) and UX-7 (last item carries action).

**Recommended resolution:** No change required. Bento-hybrid with skill-state groups + savebar status strip is compliant.

**Priority:** P3 (no change)

**Decision status:** No change required

**Acceptance criteria:** Four skill states retained; savebar status strip with no primary; one commit action.

---

### Screen: SCR-CAR-017 — Career Timeline (`/training-runs/{run}/timeline`, `runs.timeline`)

**Current layout classification:** Conventional (timeline/list)

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.26 (SCR-CAR-017 row), DESIGN.md §2.3.

**Current structure:** PageHeader. Read-only timeline; corrections flagged by timestamp comparison (no prior-values column). `◉`/`○` glyphs reserved for Phase E deadlines. Corrections made on Cockpit (SCR-CAR-011); no write route here.

**What works:** Chronological list; read-only semantics clear; corrections redirected to Cockpit.

**What is inconsistent:** None material.

**Recommended resolution:** No change required. Timeline is inherently conventional — bento would fragment the sequence.

**Priority:** P3 (no change)

**Decision status:** No change required

**Acceptance criteria:** Chronological order retained; no write route; corrections link to Cockpit.

---

### Screen: SCR-CAR-018 — Career Result (`/training-runs/{run}/result`, `runs.result`)

**Current layout classification:** Bento-hybrid

**Design compliance:** Compliant (P2 refinement noted)

**Source authority:** SCREEN_SPEC §4.26 (SCR-CAR-018 row), DESIGN.md §2.3 (`.tiles.g-4` KPI strip).

**Current structure:** PageHeader. KPI strip: `.tiles g-4` — four facts (finale, turn, races, skill points). Save Veteran door to SCR-VET-003. Deficit is `TrainerAdvisor::deficits()`. "Build quality" omitted (held computation). Export sentence previously stale — now links both downloads derived from `run_url` = `route('runs.cockpit')`.

**What works:** KPI strip aligned via `.tiles.g-4`; Save Veteran door correctly placed; export links derived from cockpit URL.

**What is inconsistent (P2):** Export card copy was stale ("No export route is built") — fixed to link both downloads. No layout defect.

**Recommended resolution:** No layout change. Keep KPI strip + Save Veteran door + export links.

**Priority:** P2 (copy fix already landed; no layout work)

**Decision status:** No change required

**Acceptance criteria:** `.tiles.g-4` KPI strip retained; export links point to `runs.export/{format}`; Save Veteran door to `runs.veteran`.

---

## Area: Scenario panels (mounted inside Cockpit)

These have no standalone route; assessed as sub-components of SCR-CAR-011 Cockpit.

### Screen: SCR-CAR-019 — Scenario panel shell

**Current layout classification:** Component (no standalone route)

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.26 (SCR-CAR-019 row).

**Current structure:** Branch-free registry per gate G-33; widgets fall to `ResourceMeter` or `WidgetFallback`; `career_goals` → `UraPanel.vue` (SCR-CAR-020); flags E3/E4 fall to `WidgetFallback`.

**What works:** Registry pattern avoids renderer sprawl; fallbacks graceful.

**What is inconsistent:** None material.

**Recommended resolution:** No change.

**Priority:** P3

**Decision status:** No change required

---

### Screen: SCR-CAR-020 — URA panel (SCREEN-014)

**Current layout classification:** Component (no standalone route)

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.26 (SCR-CAR-020 row).

**Current structure:** Three modules, two named absences. Career goals unrecorded (no `trainee_goals` table) → `N/A` with reason. Happy Meek no stored level → `N/A`. Mandatory race set has real data via `RaceCatalogSlot::isAtOrBeforeTurn()`.

**What works:** Named absences with reasons; real data where available.

**What is inconsistent:** None material.

**Recommended resolution:** No change.

**Priority:** P3

**Decision status:** No change required

---

### Screen: SCR-CAR-021 — Unity Cup panel (SCREEN-015, Team Cockpit)

**Current layout classification:** Component (no standalone route)

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.26 (SCR-CAR-021 row), HFW override register §8.25 (Unity Cup omits "Priority" label — owner-suppressed to avoid stating due obligation twice).

**Current structure:** Read-only. No UI write records team rank/burst state/Spirit reading → all `N/A` or named absence. Burst-band table renders with Extreme-counting caveat (run report §8.2). HFW shows only "Recommended" (no Priority line).

**What works:** Read-only coherence; `N/A` for unrecorded figures; owner-suppressed Priority label is deliberate.

**What is inconsistent:** None material.

**Recommended resolution:** No change.

**Priority:** P3

**Decision status:** No change required

---

### Screen: SCR-CAR-022 — Trackblazer panel (SCREEN-016)

**Current layout classification:** Component (no standalone route)

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.26 (SCR-CAR-022 row).

**Current structure:** Read-only mirror of `TurnEvents\ShopPurchasePayload`, `TrainingRun::gradePeriods()`, `TrainingRun::epithetProgress()`. Catalogue in config order; no `recommended` flag / "best value" sort / default selection; shop rotation named absent in copy. Cockpit action area gains "Shop" button (Trackblazer only) that scrolls and focuses Shop heading. Finale name "Twinkle Star Climax" is §7 conflict row 31 (UNVERIFIED) → named absence, never a string.

**What works:** Read-only mirror; named absence for unverified finale name; Shop button scrolls+focuses.

**What is inconsistent:** None material.

**Recommended resolution:** No change.

**Priority:** P3

**Decision status:** No change required

---

### Screen: SCR-CAR-023 — Scenario Race Planner (`/training-runs/{run}/races/planner`, `runs.races.planner`)

**Current layout classification:** Conventional (calendar/planner)

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.26 (SCR-CAR-023 row), DESIGN.md §2.3 (calendar wide table scrolls in focusable region below 768px).

**Current structure:** PageHeader. Whole calendar in four groups (mandatory/upcoming/optional/rival — rival is named absence, no column marks rival races). Reward comparison of up to four selected races with ten aligned rows. Target alignment in plain words (no score, no percentage). Win probability/expected risk `N/A` with ADR-0016 blocker in `title`. Deadline region states next obligation + turn.

**What works:** Four-group calendar; ten-row aligned reward comparison; `N/A` for unsourced win probability/risk; deadline region honest.

**What is inconsistent:** None material. Calendar is a wide table — deliberate horizontal scroll below 768px (held contract).

**Recommended resolution:** No change. Conventional calendar/planner layout is correct; bento would fragment the timeline.

**Priority:** P3

**Decision status:** No change required

**Acceptance criteria:** Four calendar groups retained; ten-row comparison; `N/A` with ADR-0016 title; rival group named absence.

---

### Screen: SCR-CAR-024 — Our Grand Concert baseline strip (SCR-017)

**Current layout classification:** Component (no standalone route)

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.26 (SCR-CAR-024 row), DESIGN.md §2.3 (dated exception W.1 = C — plain `h2`, no `CapsuleHeader`).

**Current structure:** Drawn where every panel flag is off (fourth `[Global]` scenario's whole surface). Badge `partially_documented` (owner ruling). Five published caps render base + bonus + cap via `ScenarioCaps`. Scenario's own one-line reason panels are off from config. No song/lesson/token string.

**What works:** Baseline strip is the correct fallback when no panel composes; caps via `ScenarioCaps`; reason from config.

**What is inconsistent:** None material.

**Recommended resolution:** No change.

**Priority:** P3

**Decision status:** No change required

---

### Screen: SCR-CAR-025 — Career Snapshot: entry (`/career/snapshot`, `career.snapshot`)

**Current layout classification:** Conventional (entry)

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.26 (SCR-CAR-025 row).

**Current structure:** Second door beside the wizard's — an already-under-way career enters at its own snapshot's position rather than replaying steps 1-3. Read-only until POST commits.

**What works:** Read-only entry; honest about snapshot position.

**What is inconsistent:** None material.

**Recommended resolution:** No change.

**Priority:** P3

**Decision status:** No change required

---

### Screen: SCR-CAR-026 — Career Snapshot: setup (`/career/snapshot/setup`, `career.snapshot.setup`)

**Current layout classification:** Conventional (form)

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.26 (SCR-CAR-026 row).

**Current structure:** Position and current state read by entry, edited on their own screen.

**What works:** Dedicated edit screen for snapshot position/state.

**What is inconsistent:** None material.

**Recommended resolution:** No change.

**Priority:** P3

**Decision status:** No change required

---

### Screen: SCR-CAR-027 — Career Snapshot: review (`/career/snapshot/review`, `career.snapshot.review`)

**Current layout classification:** Conventional (review/confirm)

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.26 (SCR-CAR-027 row).

**Current structure:** Last read before `store` commits the snapshot.

**What works:** Review-before-commit pattern; no writes on this screen.

**What is inconsistent:** None material.

**Recommended resolution:** No change.

**Priority:** P3

**Decision status:** No change required

---

### Screen: SCR-CAR-028 — Career Team setup (`/training-runs/{run}/team`, `runs.team`)

**Current layout classification:** Bento-hybrid

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.26 (SCR-CAR-028 row).

**Current structure:** PageHeader. Gates on scenario's `composesPanel('team_rank_ladder')` capability flag (never a name); runs without team capability redirect to `runs.cockpit` with flash. Mounts `UnityCupEditor` unchanged with `action` (`runs.update`) and `status` props. Editor owns its submit; page adds no savebar.

**What works:** Capability-flag gating (not name-based); no duplicate savebar; editor owns submit.

**What is inconsistent:** None material.

**Recommended resolution:** No change.

**Priority:** P3

**Decision status:** No change required

**Acceptance criteria:** Capability flag gate retained; no savebar duplication; editor owns submit.

---

### Screen: SCR-CAR-029 — Career Scenario detail (`/training-runs/{run}/scenario`, `runs.scenario-detail`)

**Current layout classification:** Bento-primary (panel grid)

**Design compliance:** Compliant (approved exception W.1 = C)

**Source authority:** SCREEN_SPEC §4.26 (SCR-CAR-029 row), DESIGN.md §2.3 (dated exception 2026-11-10: plain `h2`, no `CapsuleHeader`), HFW `scenario-detail-hfw-2026-11-09/scenario-detail-hfw.html`, owner ruling W.1 = C, W.2 = run-state primary.

**Current structure:** PageHeader (eyebrow "Scenario", title "Scenario detail"). Hosts `ScenarioPanel` for every scenario (keeps `CareerGoalsPanel` hosted now that disclosure is gone). 12-track card grid per HFW drawing: Team Race, Unity Cup progress, Burst count bands, Special Training, Trainee goals, Mandatory races, Team race coverage, Team race shape, Team, Spirit, Team Rank, Team stat grades. Reference strip (`Unity Cup`, `Published stat caps`) outside the grid. Every card heads with plain `h2`; no `CapsuleHeader`. Four sub-12 rows are the drawing's own holes, kept deliberately (row-3 closure reverted by ruling). `UnityCupPanel` keeps two registrations, delegates grid. `CareerGoalsPanel` draws URA/Our Grand Concert goals, stands down for Unity Cup.

**What works:** 12-track card grid matches HFW spans/order; plain `h2` cards per owner ruling; reference strip outside grid; sub-12 holes kept deliberately.

**What is inconsistent:** None material — the plain-`h2` exception is owner-authorized (W.1 = C) and documented in DESIGN.md §2.3 dated exception.

**Recommended resolution:** No change. This is the approved exception structure; do not add `CapsuleHeader` or argyle lattice here.

**Layout sketch:**
```
Desktop:
[PageHeader: eyebrow Scenario | h1 "Scenario detail" | lede]
[12-track card grid: g-4 at 845px+, g-2 at 585px+, 1 col below]
[Reference strip: Unity Cup | Published stat caps] (outside grid)
Tablet/Mobile: cards stack 1 column; reference strip below
```

**Implementation location:** `resources/js/pages/Career/ScenarioDetail.vue`; `resources/js/components/scenario/ScenarioCardGrid.vue`; `resources/js/components/scenario/UnityCupPanel.vue`; `resources/js/components/career/CareerGoalsPanel.vue`.

**Priority:** P3 (compliant exception)

**Decision status:** Approved exception (W.1 = C) — no change

**Acceptance criteria:** 12 cards in HFW order/spans; plain `h2` headers only; no `CapsuleHeader`; reference strip outside grid; sub-12 holes retained.

---

## Area: Career — Builders & legacy

### Screen: SCR-CAR-006 — Legacy Lab (`/legacy`, `legacy.index`; `/legacy/{run}`, `legacy.builder`; `/legacy/compare`, `legacy.compare`)

**Current layout classification:** Bento-hybrid (index/builder), Conventional (compare table)

**Design compliance:** Compliant (with HFW overrides)

**Source authority:** SCREEN_SPEC §4.25 (SCR-CAR-006 row), HFW override register §8.25 (Legacy builder rows), DESIGN.md §2.3.

**Current structure:** Index: browse runs. Builder: each parent's Sparks stay editable rows (Kind/Applies to/Stars/Remove Spark) with pick radio as row's last control — wireframe draws read-only table, but this is the only place a Spark is written. Grandparent editors (`AncestorEditor`) stay visible under each parent. `Carries` column not drawn (no per-Spark cap figure). Compare: checkbox picker above comparison (run pair arrives in `?runs[]`); all sixteen property rows kept (wireframe draws six — IA-4 density exemption).

**What works:** Editable Spark rows keep write path; grandparent editors visible; compare uses one table with Difference column; picker shows four-run cap.

**What is inconsistent:** HFW overrides recorded (structure deliberately diverges from wireframe to preserve writes and recorded data). These are owner-authorized exemptions (IA-4).

**Recommended resolution:** No change to structure. Overrides are documented and authorized. Do not convert editable Spark rows to read-only table or drop compare rows.

**Priority:** P3 (overrides authorized)

**Decision status:** Approved exception via HFW overrides + IA-4 density exemption

**Acceptance criteria:** Spark rows remain editable; grandparent editors visible; compare keeps all sixteen property rows; four-run cap picker retained.

---

### Screen: SCR-CAR-007 — Support Deck Builder (`/training-runs/{run}/deck`, `runs.deck`)

**Current layout classification:** Bento-hybrid

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.25 (SCR-CAR-007 row), DESIGN.md §2.3, lore §6 (Deck/ownership).

**Current structure:** PageHeader. Run-scoped deck builder. Equip one open picker, closed rows post hidden inputs. Ownership flag client-side only (disclosed); `deck_slots.ownership` nullable column per ADR-0023 (D3, `fdebcc9`).

**What works:** Run-scoped; picker + closed rows pattern; ownership disclosed as client-side.

**What is inconsistent:** None material.

**Recommended resolution:** No change.

**Priority:** P3

**Decision status:** No change required

**Acceptance criteria:** Picker + closed rows retained; ownership flag disclosed; run-scoped.

---

## Area: Career — Run utility/detail (registered 2026-11-18)

### Screen: SCR-CAR-030 — Run settings (`/training-runs/{run}/settings`, `runs.settings`)

**Current layout classification:** Conventional (form)

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.26 (SCR-CAR-030 row).

**Current structure:** PageHeader. Read page; every field writes through one `runs.update` owner (`CockpitController::settings` → `Career/RunSettings`).

**What works:** Single write owner; read page semantics clear.

**What is inconsistent:** None material.

**Recommended resolution:** No change.

**Priority:** P3

**Decision status:** No change required

---

### Screen: SCR-CAR-031 — Confirm delete (run) (`/training-runs/{run}/delete`, `runs.confirm-delete`)

**Current layout classification:** Conventional (confirmation)

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.26 (SCR-CAR-031 row).

**Current structure:** PageHeader (eyebrow "Delete this career"). Names cascading children (turns, race entries, turn events, skills, deck slots, linked Veteran) before destructive step. `DELETE` on `runs.destroy` (`Career/ConfirmDelete`).

**What works:** Destructive action preceded by cascade naming; separate from navigation.

**What is inconsistent:** None material.

**Recommended resolution:** No change.

**Priority:** P3

**Decision status:** No change required

---

### Screen: SCR-CAR-032 — Turn correction (`/training-runs/{run}/turns/{turn}/edit`, `runs.turns.edit`)

**Current layout classification:** Conventional (form)

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.26 (SCR-CAR-032 row).

**Current structure:** PageHeader (eyebrow "Correct turn", h1 "Turn N", lede). Read page; form PUTs to one `runs.turns.update` owner (`Career/TurnEdit`).

**What works:** Single update owner; turn-scoped edit; no duplicate write paths.

**What is inconsistent:** None material.

**Recommended resolution:** No change.

**Priority:** P3

**Decision status:** No change required

---

## Area: Runs (SCR-RUN-*)

### Screen: SCR-RUN-001 — Training runs list (`/training-runs`, `runs.index`)

**Current layout classification:** Conventional (table/list)

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.1 (SCR-RUN-001 row), DESIGN.md §2.3 (table rows `py-1.5 px-3`, pagination under tables).

**Current structure:** PageHeader (eyebrow "Careers", title "Training runs"). Action row: text link "Import a historical run" + primary pill "New run". Run list (newest first) or empty state. Pagination links. Per row: trainee name, scenario label (after `·`), status label, creation date. Empty: "No runs yet … there is nothing to list until you start one."

**What works:** Table/list is correct for a run inventory; empty state frames as real state; pagination under table; scenario label from config only (D-240).

**What is inconsistent:** None material.

**Recommended resolution:** No change. Conventional table layout is correct; bento would fragment the list.

**Priority:** P3

**Decision status:** No change required

**Acceptance criteria:** Table retained; pagination under table; empty state names remedy; scenario label from config.

---

### Screen: SCR-RUN-002 — New training run (`/training-runs/create`, `runs.create`)

**Current layout classification:** Conventional (form)

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.2 (SCR-RUN-002 row), DESIGN.md §2.3 (`max-w-lg` single-column form).

**Current structure:** PageHeader. Single form card: trainee combobox, scenario select, status select, inheritance A/B selects, Grade Point period number, Shop resets number, notes textarea, submit. `max-w-lg`. Combobox is the control (dual fallback retired).

**What works:** Single-column narrow form; selects over free text; one submit.

**What is inconsistent:** None material.

**Recommended resolution:** No change.

**Priority:** P3

**Decision status:** No change required

**Acceptance criteria:** `max-w-lg` form; one submit; combobox control.

---

### Screen: SCR-RUN-003 — Run detail & turn log (`/training-runs/{run}`, `runs.show`)

**Current layout classification:** N/A — Retired 2026-10-09 (F2)

**Design compliance:** N/A

**Source authority:** SCREEN_SPEC §4.3 (retirement note).

**Current structure:** `GET /training-runs/{run}` 302s to `runs.cockpit` (SCR-CAR-011). `{run}` still binds; missing id 404s.

**What works:** Redirect sends users to the 2.0 Cockpit directly.

**What is inconsistent:** None — retired by design.

**Recommended resolution:** No change. No standalone render to assess.

**Priority:** N/A

**Decision status:** No change required

---

### Screen: SCR-RUN-004 — Import a historical run (form) (`/training-runs/import`, `runs.import`)

**Current layout classification:** Conventional (form)

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.4 (SCR-RUN-004 row), DESIGN.md §2.3.

**Current structure:** PageHeader. Import form (CSV from finished career). Back button from import.

**What works:** Single import form; back button.

**What is inconsistent:** None material.

**Recommended resolution:** No change.

**Priority:** P3

**Decision status:** No change required

**Acceptance criteria:** Single form; back button; CSV import semantics unchanged.

---

### Screen: SCR-RUN-005 — Import preview & confirm (`POST /training-runs/import/preview`, `runs.import.preview`)

**Current layout classification:** Conventional (preview table)

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.5 (SCR-RUN-005 row), DESIGN.md §2.3 (table.t grammar).

**Current structure:** Preview table of imported data; confirm action.

**What works:** Table preview before commit; confirm action clear.

**What is inconsistent:** None material.

**Recommended resolution:** No change.

**Priority:** P3

**Decision status:** No change required

**Acceptance criteria:** Preview table; confirm action; no commit without preview.

---

## Area: Catalog (SCR-CAT-*)

### Screen: SCR-CAT-001 — Umamusume catalog (`/umamusume`, `catalog.index`)

**Current layout classification:** Conventional (roster tree/list)

**Design compliance:** Compliant

**Source authority:** DESIGN.md §4.1 (catalog index), SCREEN_SPEC §4.5 (SCR-CAT-001 row), DESIGN.md §2.3.

**Current structure:** PageHeader. Two-level roster tree: one card per trainee (`<li>` with bordered `bg-raised` surface) — name, name_ja, max rarity chip, form count, release status. Body: `divide-y` list of costume forms (title, rarity chip, debut marker, confirmation warning, Global release date). Filter row: single inline GET form (search, release status, unconfirmed opt-in, submit). Empty: dashed panel naming remedy (seed or `uma:fetch`).

**What works:** Tree (not table) is correct — search matches forms/aliases, so match stays visible at the level it occurred. Filter row is single inline form. Empty names remedy.

**What is inconsistent:** None material.

**Recommended resolution:** No change. Conventional roster tree is correct; card grid would hide the matched form.

**Priority:** P3

**Decision status:** No change required

**Acceptance criteria:** Roster tree retained; filter row single inline form; empty state names remedy.

---

### Screen: SCR-CAT-002 — Umamusume profile detail (`/umamusume/{slug}`, `catalog.show`)

**Current layout classification:** Bento-hybrid (identity header + sections)

**Design compliance:** Compliant

**Source authority:** DESIGN.md §4.2 (catalog detail ported structure), SCREEN_SPEC §4.5 (SCR-CAT-002 row).

**Current structure:** PageHeader. Eight sections: Identity, Aptitudes, Skills, Costume forms, Goal races, Her runs, Aliases, Provenance. ArtworkSlot size-16 (detail header, no reserve). Primary action "New run for <name>" in Her runs section. Absence: `N/A` with title.

**What works:** Workspace structure; eight sections in order; provenance last and quiet; `N/A` discipline; artwork slot geometry pinned (size-16, no reserve on detail header).

**What is inconsistent:** None material. Sections 3-5 say "not yet recorded" — honest absence pattern.

**Recommended resolution:** No change. Bento-hybrid (identity header + sectioned body) is correct for a trainee workspace.

**Priority:** P3

**Decision status:** No change required

**Acceptance criteria:** Eight sections in order; artwork size-16 no reserve; `N/A` with titled reason; primary action "New run for <name>".

---

## Area: Skills (SCR-SKL-*)

### Screen: SCR-SKL-001 — Skill search (`/skills`, `skills.index`)

**Current layout classification:** Conventional (search results list)

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.6 (SCR-SKL-001 row), DESIGN.md §2.3 (skill filter form all `h-11`).

**Current structure:** PageHeader. Skill filter form: search, type, unique-only, all `h-11`. Skill rows: name, name_ja, Unique mark, type, SP cost. Empty: invitation shown only with no query; two distinct no-data states.

**What works:** Filter row single inline form; all controls `h-11` (44px); two distinct empty states.

**What is inconsistent:** None material.

**Recommended resolution:** No change. Conventional search-results list is correct.

**Priority:** P3

**Decision status:** No change required

**Acceptance criteria:** Filter row retained; all controls `h-11`; two empty states distinct.

---

### Screen: SCR-SKL-002 — Skill detail (`/skills/{skill}`, `skills.show`)

**Current layout classification:** Bento-hybrid (detail + sections)

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.6 (SCR-SKL-002 row), DESIGN.md §2.3.

**Current structure:** PageHeader. Skill detail sections. (Source detail limited in SCREEN_SPEC; assessed as conventional detail page.)

**What works:** Detail page structure.

**What is inconsistent:** None material.

**Recommended resolution:** No change.

**Priority:** P3

**Decision status:** No change required

**Acceptance criteria:** Detail page retains sections; `N/A` discipline for absent values.

---

## Area: Support cards (SCR-SUP-*)

### Screen: SCR-SUP-001 — Support-card catalog (`/support-cards`, `support-cards.index`)

**Current layout classification:** Conventional (catalog list)

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.7 (SCR-SUP-001 row), DESIGN.md §2.3.

**Current structure:** PageHeader. Support catalog list. ArtworkSlot size-12 (index row, reserve, navigates to detail).

**What works:** Catalog list; artwork geometry pinned (size-12, reserve on index).

**What is inconsistent:** None material.

**Recommended resolution:** No change.

**Priority:** P3

**Decision status:** No change required

**Acceptance criteria:** Catalog list retained; artwork size-12 with reserve.

---

### Screen: SCR-SUP-002 — Support-card detail (`/support-cards/{card}`, `support-cards.show`)

**Current layout classification:** Bento-hybrid (artwork frame + detail sections)

**Design compliance:** Compliant (HFW override: artwork frame position)

**Source authority:** SCREEN_SPEC §4.7 (SCR-SUP-002 row), HFW override register §8.25 (support-card detail artwork position), DESIGN.md §4.7, §2.3.

**Current structure:** PageHeader (eyebrow "Support card", title card name). 64px artwork frame stays in its own row under the sub-nav (wireframe puts it inside head row leading the title — override: `PageHeader` exposes one `actions` slot and no title-leading slot; position parity would require editing a 29-importer component pinned by `page-header.spec.ts`). Absence: file mirror doesn't hold → renders nothing (no broken frame). Effects table: prints `1: Not recorded` when source states nothing at level 1 (2,756 of 5,114 effect rows have no level-1 anchor). `Event | Chain | Choice` card not drawn (schema stores skill lists, not card events — inventing event names forbidden).

**What works:** Artwork frame in own row (override documented); effects table prints `Not recorded` for missing level-1; no fabricated event card.

**What is inconsistent:** HFW override — artwork frame position diverges from wireframe but is sanctioned (PageHeader slot constraint). Not a defect.

**Recommended resolution:** No change. Override is documented and authorized.

**Layout sketch:**
```
[PageHeader: eyebrow | h1 card name | actions]
[Artwork frame row: 64px frame | (title in header)]
[Effects table: level rows, "Not recorded" where absent]
[Hinted/Event skill cards]
```

**Implementation location:** `resources/js/pages/SupportCards/Show.vue`; `resources/js/components/ui/PageHeader.vue`.

**Priority:** P3 (override authorized)

**Decision status:** Approved exception (HFW override — PageHeader slot constraint)

**Acceptance criteria:** Artwork frame remains in own row under sub-nav; effects table prints `Not recorded`; no fabricated `Event | Chain | Choice` card.

---

## Area: Review (SCR-REV-*)

### Screen: SCR-REV-001 — Match review queue (`/review`, `review.index`)

**Current layout classification:** Conventional (queue/cards)

**Design compliance:** Compliant

**Source authority:** DESIGN.md §4.5 (review queue), SCREEN_SPEC §4.8 (SCR-REV-001 row), DESIGN.md §5 (one absence channel per fact).

**Current Structure:** PageHeader. Queue density: newest candidates first. Tier badge (Fuzzy/None) and source key always visible. Resolve controls inline per card (no detail page). Resolved rows leave the view (Pending filter); history is row status, not separate screen. Empty: "Nothing pending" + command. Error: stacked `role="alert"` list.

**What works:** Inline resolve controls; tier badge + source key visible; no detail page (correct for a queue); empty state names remedy.

**What is inconsistent:** None material. Historical double-print (StatusPill + ProvenanceBadge) fixed per one-provenance-label amendment.

**Recommended resolution:** No change. Conventional queue layout is correct; cards are candidate rows, not bento panels.

**Priority:** P3

**Decision status:** No change required

**Acceptance criteria:** Inline resolve controls; tier badge + source key per row; "Nothing pending" empty state; no detail page.

---

### Screen: SCR-REV-002 — Match review detail (`/review/{candidate}`, `review.detail`)

**Current layout classification:** Conventional (resolve form)

**Design compliance:** Compliant (HFW override: verdict `Proposed` not `Chosen`)

**Source authority:** SCREEN_SPEC §4.8 (SCR-REV-002 row), HFW override register §8.25 (review detail), DESIGN.md §5(b) (one provenance-state label per fact).

**Current Structure:** PageHeader (eyebrow "Review", heading). Back link in head band (per `review-detail.html:61-63`). Other-candidates table marks engine's proposal `Proposed` (wireframe's first row carries `Chosen` — override: proposal is not Trainer's decision; verdict form opens unset; `Use this` is the way to adopt). `.g-2` band is sanctioned container-query port.

**What works:** `Proposed` marking is truthful; back link in head band; one state label per card.

**What is inconsistent:** HFW override documented — `Proposed` vs `Chosen` is owner-authorized. Not a defect.

**Recommended resolution:** No change.

**Priority:** P3 (override authorized)

**Decision status:** Approved exception (HFW override)

**Acceptance criteria:** `Proposed` pill retained; verdict form opens unset; `Use this` action; one state label per row.

---

## Area: Veterans (SCR-VET-*)

### Screen: SCR-VET-001 — Veteran library (`/veterans`, `veterans.index`)

**Current layout classification:** Conventional (library list)

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.16 (SCR-VET-001 row), DESIGN.md §2.3.

**Current Structure:** PageHeader. Veteran library list. Favorite/archive/delete and Spark-graded sorts are named absences.

**What works:** Library list structure; named absences honest.

**What is inconsistent:** None material.

**Recommended resolution:** No change.

**Priority:** P3

**Decision status:** No change required

**Acceptance criteria:** Library list retained; absences named.

---

### Screen: SCR-VET-002 — Veteran detail (`/veterans/{veteran}`, `veterans.show`)

**Current layout classification:** Bento-hybrid (detail + context)

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.17 (SCR-VET-002 row), DESIGN.md §2.3.

**Current Structure:** PageHeader (eyebrow "Veteran", title trainee + name_ja, lede). Six-node graph is one link away in Legacy Lab (not re-rendered here).

**What works:** Detail page; graph link to Legacy Lab (correct separation).

**What is inconsistent:** None material.

**Recommended resolution:** No change.

**Priority:** P3

**Decision status:** No change required

**Acceptance criteria:** Detail page retained; graph link to Legacy Lab; `N/A` discipline.

---

### Screen: SCR-VET-003 — Save Veteran (`/training-runs/{run}/veteran`, `runs.veteran` + store)

**Current layout classification:** Conventional (save form)

**Design compliance:** Compliant (built; browser spec unrun)

**Source authority:** SCREEN_SPEC §4.19 (SCR-VET-003 row).

**Current Structure:** Built, not landed at audit time (gate green, browser spec written and unrun). Factor analysis, legacy value, best use held (`ADR-0020` §3) → print `N/A` with ruling.

**What works:** Save form structure; `N/A` for held values.

**What is inconsistent:** None material. Browser spec unrun — layout not visually verified.

**Recommended resolution:** No change. Conventional save form is appropriate.

**Priority:** P3 (unverified — browser spec unrun)

**Decision status:** No change required (pending browser verification)

**Acceptance criteria:** Save form; `N/A` with titled reason for held values; browser spec green on run.

---

### Screen: SCR-VET-004 — Veteran comparison (`/veterans/compare`, `veterans.compare`)

**Current layout classification:** Conventional (comparison matrix)

**Design compliance:** Unverified (gate RED at audit time)

**Source authority:** SCREEN_SPEC §4.20 (SCR-VET-004 row), HFW override register §8.25 (veteran comparison rows), DESIGN.md §2.3 (`table.t`, `.card:has(> table.t)`).

**Current Structure:** PageHeader (eyebrow "Veteran comparison"). Matrix: first column + header row sticky (override: wireframe draws no sticky region — 24 rows × up to 6 careers, sticky needed for readability). Per-subject `Best for` row not drawn (no source prices a verdict — wireframe keybox item 3 forbids recommendation engine). Matrix keeps all property rows app stores (wireframe draws six — IA-4 density exemption). `NOT IN THIS BUILD` names uncomputed figures.

**What works:** Sticky matrix is correct for wide comparison; `Best for` absence is honest; all property rows kept per IA-4.

**What is inconsistent:** Gate RED — `Compare.vue:85` sends `router.get` key `'veterans[]'` over an array, request refused, `back()` lands on `/veterans`. Two-column render unasserted in browser. This is a data-submission defect, not a layout defect — but it blocks visual verification.

**Recommended resolution:** No layout change. Fix the array serialization (`veterans[0]=N` vs `veterans[]=N`) — backend/frontend, out of audit scope. Layout (sticky matrix, all property rows) is correct.

**Layout sketch:**
```
[PageHeader]
[Sticky matrix: subject col | career 1 | ... | career 6 | Difference]
[First column + header row sticky]
[NOT IN THIS BUILD notes for uncomputed figures]
```

**Implementation location:** `resources/js/pages/Veterans/Compare.vue`; `resources/css/layout-grid.css` (sticky via `table.t`/`card:has(> table.t)`).

**Priority:** P0 (gate RED — comparison matrix cannot load; data-submission defect blocking the screen)

**Decision status:** Backend-dependent (array serialization fix) — no layout change

**Acceptance criteria:** Matrix loads at `/veterans/compare`; sticky first column + header row; all property rows; `Best for` absent; `NOT IN THIS BUILD` for uncomputed.

---

### Screen: SCR-VET-005 — Transfer requests (`/veterans/transfers`, `veterans.transfers`)

**Current layout classification:** Conventional (list)

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.19 (SCR-VET-005 row), registered 2026-11-18.

**Current Structure:** TransferRequestController::index renders `Veterans/TransferRequests`. Completion/withdrawal via `/transfers/{transfer}`.

**What works:** List structure; write paths separate.

**What is inconsistent:** None material.

**Recommended resolution:** No change.

**Priority:** P3

**Decision status:** No change required

---

## Area: System / Database (SCR-SYS-*)

### Screen: SCR-SYS-001 — Page not found (404)

**Current layout classification:** Conventional (error document)

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.27 (SCR-SYS-001 row), DESIGN.md §2.2 (one h1 per page in standalone error documents).

**Current Structure:** Standalone Blade error document (no shell). Branded recovery; named routes out.

**What works:** Standalone document (cannot mount shell — 500 deliberately never touches DB); branded recovery with named routes.

**What is inconsistent:** None material.

**Recommended resolution:** No change.

**Priority:** P3

**Decision status:** No change required

**Acceptance criteria:** Branded 404 with named route exits; standalone document.

---

### Screen: SCR-SYS-002 — Preferences (`/preferences`, `preferences.edit` + update/export/backup)

**Current layout classification:** Conventional (settings form)

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.28 (SCR-SYS-002 row), DESIGN.md §2.3 (settings form).

**Current Structure:** PageHeader. Category shell + Game version panel. Four stored preferences in one `settings` row. Export stream + server-side Backup. Absence reasons named per class (Units = sourcing absence, not schema).

**What works:** Category shell; settings in one row; absence reasons named.

**What is inconsistent:** None material.

**Recommended resolution:** No change.

**Priority:** P3

**Decision status:** No change required

**Acceptance criteria:** Settings form retained; absence reasons named; export/backup paths separate.

---

### Screen: SCR-SYS-003 — Session expired (419)

**Current layout classification:** Conventional (error document)

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.29 (SCR-SYS-003 row).

**Current Structure:** Standalone error document.

**What works:** Error document.

**What is inconsistent:** None material.

**Recommended resolution:** No change.

**Priority:** P3

**Decision status:** No change required

**Acceptance criteria:** 419 error document rendered.

---

### Screen: SCR-SYS-004 — Server error (500)

**Current layout classification:** Conventional (error document)

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.30 (SCR-SYS-004 row).

**Current Structure:** Standalone document, no database read.

**What works:** Never touches DB; standalone.

**What is inconsistent:** None material.

**Recommended resolution:** No change.

**Priority:** P3

**Decision status:** No change required

**Acceptance criteria:** 500 error document without DB read.

---

### Screen: SCR-SYS-005 — Database hub (`/database`, `database.index`)

**Current layout classification:** Bento-primary (hub of eight area tiles)

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.31 (SCR-SYS-005 row), DESIGN.md §2.3 (`.tiles` KPI strip, `.g-3` launcher), DESIGN.md §4.15.

**Current Structure:** PageHeader. One `g-3` launcher: nine tiles, each a live count (or the word the area's data supplies). Links to Trainees, Support Cards, Skills, Races, Scenarios (three render shared catalog component at their own `/database/*` address, no redirect); two new: Races from `RaceCatalogSlot` (SCR-SYS-006), Scenarios from `config/scenarios.php` (SCR-SYS-007). Extended to eight area links: Events, Shop Items, Sparks. "Not in this build" section deleted (not softened).

**What works:** `g-3` tile launcher is correct bento-primary for a hub; eight area links; no fake "not in this build" softening.

**What is inconsistent:** None material.

**Recommended resolution:** No change. Database hub is the canonical bento-primary reference alongside Dashboard.

**Priority:** P3

**Decision status:** No change required

**Acceptance criteria:** `g-3` tile launcher retained; eight area links; shared catalog component at own addresses; no redirect.

---

### Screen: SCR-SYS-006 — Race database (`/database/races`, `database.races`)

**Current layout classification:** Conventional (paginated table)

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.31 (SCR-SYS-006 row), DESIGN.md §2.3 (wide table scrolls in focusable region at 320px).

**Current Structure:** PageHeader. Paginated `race_catalog_slots`. Offline state names fetch command. Unrecorded cells `N/A` with reason. Wide table scrolls inside focusable region at 320px. No win probability / readiness band (`ADR-0016`).

**What works:** Paginated table; `N/A` discipline; focusable scroll region; no fabricated win probability.

**What is inconsistent:** None material.

**Recommended resolution:** No change. Conventional table is correct for a race catalog.

**Priority:** P3

**Decision status:** No change required

**Acceptance criteria:** Paginated table retained; focusable scroll at 320px; `N/A` with reason.

---

### Screen: SCR-SYS-007 — Scenario matrix (`/database/scenarios`, `database.scenarios`)

**Current layout classification:** Conventional (cards + tables)

**Design compliance:** Compliant (HFW alignment overrides)

**Source authority:** SCREEN_SPEC §4.31 (SCR-SYS-007 row), HFW alignment note 2026-10-31, DESIGN.md §2.3.

**Current Structure:** PageHeader. Three cards in order: Caps, Panels each scenario composes, Last rework — each `div.card role="region"` holding `h2` + `table.t` + `p.note`. "Matrix verified" band gone (sentence in caps note). Per-scenario cards, `DataTable`s, `Head` intro below spine. One `p.note` above first detail wrapper naming table idiom. Overrides: panel row order (config key order), heading "Last rework" (over mock's "Ruleset dates"), rework text, caps note, no current row.

**What works:** Card + table structure; overrides documented; verification date stated once.

**What is inconsistent:** HFW overrides documented (heading, row order, no current row). Authorized.

**Recommended resolution:** No change.

**Priority:** P3 (overrides authorized)

**Decision status:** Approved exception (HFW overrides)

**Acceptance criteria:** Three cards in HFW order; `h2` + `table.t` + `p.note` per card; verification date once; no current row.

---

### Screen: SCR-SYS-008 — Events reference (`/database/events`, `database.events`)

**Current layout classification:** Conventional (catalog card + tables)

**Design compliance:** Compliant (HFW alignment override)

**Source authority:** SCREEN_SPEC §4.31 (SCR-SYS-008 row), HFW alignment note, DESIGN.md §2.3 (`card:has(> table.t)` scroll region at 320px).

**Current Structure:** PageHeader. Bare catalogue card: `div.card` wrapping `table.t` (aria-label accessible name), drawer on event name. Two reference cards below in `g-2`. App-only intro + SourceNote after spine. Columns: Event/Servers/Cadence/Mechanics/Source (not mock's event chains). `card:has(> table.t)` makes card the scroll region at 320px.

**What works:** Card as scroll region; columns per §4.4; `aria-label` on table.

**What is inconsistent:** HFW override (table columns per app's §4.4, not mock). Authorized.

**Recommended resolution:** No change.

**Priority:** P3 (override authorized)

**Decision status:** Approved exception (HFW override)

**Acceptance criteria:** Card wraps table.t; `aria-label` accessible name; `g-2` for reference cards; scroll region at 320px.

---

### Screen: SCR-SYS-009 — Shop Items reference (`/database/shop-items`, `database.shopItems`)

**Current layout classification:** Conventional (catalog card + tables)

**Design compliance:** Compliant (HFW alignment overrides)

**Source authority:** SCREEN_SPEC §4.31 (SCR-SYS-009 row), HFW alignment note.

**Current Structure:** PageHeader. Bare Pro Shop card: `div.card` wrapping `table.t` (aria-label), client's pager under it right-aligned. Shop rules + What the ratings cards below in `g-2`. App-only intro + SourceNote + catalogue `DataTable` + drawer after spine. One sentence above catalogue names which table idiom is which. Ratings tiles use ported `g-3`.

**What works:** Two table idioms with explanatory sentence; `g-3` ratings tiles; pager right-aligned.

**What is inconsistent:** HFW overrides (Pro Shop card structure, table idiom note). Authorized.

**Recommended resolution:** No change.

**Priority:** P3 (override authorized)

**Decision status:** Approved exception (HFW override)

**Acceptance criteria:** Pro Shop card wraps table.t; pager right-aligned; `g-3` ratings; table idiom sentence present.

---

### Screen: SCR-SYS-010 — Sparks reference (`/database/sparks`, `database.sparks`)

**Current layout classification:** Conventional (cards + table)

**Design compliance:** Compliant (HFW alignment overrides)

**Source authority:** SCREEN_SPEC §4.31 (SCR-SYS-010 row), HFW alignment note, lore §6 (no `factor` in displayed strings).

**Current Structure:** PageHeader. Families card, Blue/Pink value cards in `g-2`, then The inheritance loop. Each `div.card role="region"` with `table.t` direct child (card is scroll region). Intro + SourceNote + category `DataTable` + drawer + star-roll odds below spine. One `p.note` above first wrapper naming table idiom. Corrections: families are Blue/Pink/Green/White + scenario kind (not mock's Blue/Pink/White/Unique); Blue ladder +5/+12/+21 (not +16/+8/+5); Pink separated from Blue.

**What works:** Card-as-scroll-region; corrected family/ladder data; no `factor` in strings; `g-2` value cards.

**What is inconsistent:** HFW overrides (family names, ladder figures). Authorized data corrections.

**Recommended resolution:** No change.

**Priority:** P3 (override authorized)

**Decision status:** Approved exception (HFW override + data correction)

**Acceptance criteria:** Four families + scenario kind; Blue +5/+12/+21; no `factor` string; `g-2` value cards.

---

### Screen: SCR-SYS-011 — Sources reference (`/database/sources`, `database.sources`)

**Current layout classification:** Conventional (cards + ledger)

**Design compliance:** Compliant (HFW alignment overrides)

**Source authority:** SCREEN_SPEC §4.32 (SCR-SYS-011 row), HFW alignment note, DESIGN.md §2.3.

**Current Structure:** PageHeader. `div.info` verification band, Tiers card, Sources ledger, then Open conflicts + What this page is for in `g-2`. Every card `div.card role="region"`; every table ported `table.t` with scoped headers (card is scroll region). Ledger accessible name moved from `sr-only` caption to table `aria-label`. Overrides: tier S row (em dash replaced — em dashes banned in shipped copy), ledger columns (Kind column carries fetched-vs-transcribed distinction), conflicts, conflicts note. Data correction: mood race effect left Open conflicts list (settled 2026-09-27); tile reads three not mock's four. Both `N/A` markers gained spoken reasons.

**What works:** Card-as-scroll-region for five-column ledger; `aria-label` on table; spoken reasons on `N/A`; settled conflict removed from open list.

**What is inconsistent:** HFW overrides documented. Authorized.

**Recommended resolution:** No change.

**Priority:** P3 (override authorized)

**Decision status:** Approved exception (HFW override)

**Acceptance criteria:** Ledger card scroll region; `aria-label` accessible name; `g-2` for closing cards; spoken reasons on `N/A`.

---

### Screen: SCR-SYS-012 — Help and documentation (`/help`, `help`)

**Current layout classification:** Conventional (cards/documentation)

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.33 (SCR-SYS-012 row), §4.26 (About this build card, 2026-11-11).

**Current Structure:** PageHeader. Cards: keyboard, tags, rules, etc. Seventh card "About this build" prints app version from shared `app` props with Global-ruleset absence beside it.

**What works:** Documentation cards; About this build card; version from shared props; named absence for Global ruleset.

**What is inconsistent:** None material.

**Recommended resolution:** No change.

**Priority:** P3

**Decision status:** No change required

**Acceptance criteria:** Documentation cards retained; About this build card; Global ruleset absence named.

---

### Screen: SCR-SYS-013 — Database trainees (`/database/trainees`, `database.trainees`)

**Current layout classification:** Conventional (shared catalog list)

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.34 (SCR-SYS-013 row), registered 2026-11-18.

**Current Structure:** Shared trainee catalog list at its own address (`Database/Trainees`), not a redirect.

**What works:** Shared component at own address; no redirect.

**What is inconsistent:** None material.

**Recommended resolution:** No change.

**Priority:** P3

**Decision status:** No change required

**Acceptance criteria:** Shared catalog list at own address; no redirect.

---

### Screen: SCR-SYS-014 — Database support cards (`/database/supports`, `database.supports`)

**Current layout classification:** Conventional (shared catalog list)

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.34 (SCR-SYS-014 row).

**Current Structure:** Shared support catalog at its own address (`Database/Supports`).

**What works:** Shared component at own address.

**What is inconsistent:** None material.

**Recommended resolution:** No change.

**Priority:** P3

**Decision status:** No change required

**Acceptance criteria:** Shared catalog list at own address.

---

### Screen: SCR-SYS-015 — Database skills (`/database/skills`, `database.skills`)

**Current layout classification:** Conventional (shared catalog list)

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.34 (SCR-SYS-015 row).

**Current Structure:** Shared skill catalog at its own address (`Database/Skills`).

**What works:** Shared component at own address.

**What is inconsistent:** None material.

**Recommended resolution:** No change.

**Priority:** P3

**Decision status:** No change required

**Acceptance criteria:** Shared catalog list at own address.

---

### Screen: SCR-SYS-016 — Race detail (`/database/races/{id}`, `database.races.show`)

**Current layout classification:** Conventional (detail)

**Design compliance:** Compliant

**Source authority:** SCREEN_SPEC §4.34 (SCR-SYS-016 row).

**Current Structure:** PageHeader (eyebrow "Race", title slot title). `DatabaseController::raceDetail` renders `Database/RaceDetail`. One catalog slot's provenance and unrecorded cells render with reasons.

**What works:** Detail page; provenance + `N/A` with reasons.

**What is inconsistent:** None material.

**Recommended resolution:** No change.

**Priority:** P3

**Decision status:** No change required

**Acceptance criteria:** Detail page retained; provenance and unrecorded cells with reasons.

---

## Summary of classifications

| Classification | Count | Screens |
|---|---|---|
| Bento-primary | 4 | Dashboard, Career Cockpit, Career Scenario detail, Database hub |
| Bento-hybrid | 12 | Training Decision, Race Decision, Event Decision, Inheritance Event, Skills Planner, Career Result, Support Deck Builder, Legacy Lab (index/builder), Catalog detail, Skill detail, Support-card detail, Veteran detail, Team setup |
| Conventional | 48 | All wizard steps, runs list/create/import/preview, catalog index, skills index, support-cards index, review queue/detail, veterans library, transfer requests, race planner, timeline, all database reference pages, run settings, confirm delete, turn edit, snapshot entry/setup/review, preferences, help, error documents, scenario panels as components |
| Approved exception | 0 explicit | HFW overrides recorded on 5 career screens (Cockpit disclosure, Skills savebar, Deck no gauge, Review detail verdict, Support card detail artwork, Legacy/builder compare tables, Veteran comparison sticky matrix) — treated as compliant-with-documented-override, not reclassified |

---

## Re-validation — 2026-11-23 (modernization pass)

**Repository HEAD at re-validation:** `3600741e`
**Document baseline:** 2026-11-22
**Status:** Reviewed; no source changes affecting this document's claims since baseline.
**Limitations:** No local server available for live re-measurement; all claims rest on source-static inspection.
