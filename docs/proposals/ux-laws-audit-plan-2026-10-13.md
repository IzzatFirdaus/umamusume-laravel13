# Laws of UX audit — plan (canonical checklist, coverage matrix, instruments, output contract)

> **Reference only. Not authorization.** Nothing in this file is a decision. No component, token, copy
> string, gate or spec line was changed to produce it. Every section names the files an approval would
> touch and touches none of them.
>
> Recorded 2026-10-13. This is a **plan**, not an audit: it defines the method, the coverage matrix, the
> instruments and the output contract for auditing Trainer Desk against the Laws of UX. It is the fourth
> file in the proposal-side audit set and it **extends** the existing three rather than restating them:
> `docs/research-scratch/AUDIT-AND-VERIFICATION.md` §"cockpit-ui-audit-2026-10-12.md" owns `SCR-CAR-011`, `docs/research-scratch/AUDIT-AND-VERIFICATION.md` §"setup-wizard-ui-audit-2026-10-12.md"
> owns the six `/career/setup/*` steps, and `docs/research-scratch/AUDIT-AND-VERIFICATION.md` §"remaining-screens-ui-audit-2026-10-13.md" owns
> the other 40 pages. Their findings are the audit's **input**; this plan says how to evaluate them (and
> the screens they did not reach) against the laws, and what instrument produces each verdict.
>
> **What this plan deliberately does not do:** it does not re-run the audits, does not assign new finding
> IDs, and does not re-open any of the eleven "what a later pass must not do" items in
> `docs/research-scratch/AUDIT-AND-VERIFICATION.md` §"remaining-screens-ui-audit-2026-10-13.md" §5. It inherits them (restated in §9).

## 0. What governs this plan

Per `AGENTS.md` §2 the precedence chain that settles every dispute below is: quality bar → gate registry
→ accepted ADRs → `PRD.md` → `ARCHITECTURE.md` → `SCREEN_SPEC.md` (screen behaviour) → `DESIGN.md` (visual
system) → `docs/proposals/design-2.0.md` (reference target). The Laws of UX are a **lens over that chain,
never a replacement for it**: where a law appears to ask for something an ADR or a `SCREEN_SPEC` ruling
forbids (a score, a win-chance, a warning role, a scenario name in a component), the law loses and the
conflict is recorded, not resolved silently. Five facts from the existing files frame the whole plan:

1. **The app has its own UX spine already.** `docs/proposals/design-2.0.md` §2 states it as
   `State → Options → Recommendation → Explanation → Decision`. The laws are how we test whether each
   screen honours that spine; they are not a second spine.
2. **The visual contract is already measured in places.** `DESIGN.md` §2-§8 (tokens, type scale at
   `:188-190`, ratified widths at `:197-208`, the wide-table region rule at `:209-224`, motifs at
   `:231-248`, absence/provenance at `:567-624`, motion at `:679-726`, WCAG 2.2 AA floor at `:851-872`).
   A law that is really a contract question is settled by the contract, not by taste.
3. **The three audits already produced a score card.** `docs/research-scratch/AUDIT-AND-VERIFICATION.md` §"remaining-screens-ui-audit-2026-10-13.md" §1.1
   scores eight clusters on impeccable's five dimensions (Accessibility, Performance, Theming,
   Responsive, Implementation Integrity), 104/160; §1.2 counts 1 P0, 26 P1, 31 P2, 29 P3. This plan adds
   a **laws** axis on top of that score card; it does not rescore the clusters.
4. **The bar cannot see several classes the laws travel on.** `docs/research-scratch/AUDIT-AND-VERIFICATION.md` §"remaining-screens-ui-audit-2026-10-13.md"
   §4 is the list: `npm run typecheck` reads no `.vue` file (§4.1), the copy-hygiene guard sees no
   server-composed string (§4.2), the G-18 contrast contract pins 19 pairs with one `ink-muted` pair
   (§4.3), registers lag the code (§4.4), an unvalidated config→badge index exists (§4.5), and the
   deterministic slop detector returned `[]` (§4.6). The plan's instruments (§4) are chosen to reach the
   channels those gaps leave dark.
5. **The 2026-10-13 live capture already corroborates two existing findings.** The session-private
   harness (§4.2) rendered the setup wizard with every later step marked `done` while nothing was entered
   (`SetupLayout.vue:45-46`; corroborates `docs/research-scratch/AUDIT-AND-VERIFICATION.md` §"setup-wizard-ui-audit-2026-10-12.md" §2.2), and rendered
   Preflight's five warnings beside the same all-`done` bar. Those are the first pixel-level confirmations
   in this corpus; the plan makes that harness the default measurement instrument, and the pass's own laws
   report is reconciled in §2.9.

## 1. The canonical checklist this audit applies (verbatim)

This is the checklist the audit is run against, reproduced exactly as supplied. It is the audit's spine;
§3 maps each row to a repo-grounded check and an instrument.

### 1.1 The laws and principles

````text
Below is a practical checklist based on the Laws of UX collection: https://lawsofux.com/ ## UX laws and principles 1. **Aesthetic–Usability Effect** — Attractive interfaces are perceived as easier to use. Use intentional visual hierarchy, spacing, typography, contrast, and polished states; do not let aesthetics hide usability problems. 2. **Jakob's Law** — Users expect interfaces to work like familiar products. Use recognizable navigation, buttons, forms, filters, tables, dialogs, and wizard conventions. 3. **Fitts's Law** — The time to reach a target depends on its size and distance. Make primary actions large enough, keep related controls close, and provide generous touch targets. 4. **Hick's Law** — More choices increase decision time. Reduce simultaneous options, group them, use progressive disclosure, and provide sensible defaults. 5. **Miller's Law** — Working memory is limited. Chunk information into meaningful groups; do not treat "7±2" as a literal UI quota. 6. **Law of Proximity** — Nearby elements are perceived as related. Keep labels close to fields, actions close to the content they affect, and separate unrelated sections with spacing. 7. **Law of Similarity** — Similar-looking elements are perceived as having similar roles. Keep equivalent controls visually consistent and avoid making different actions look identical. 8. **Law of Common Region** — Elements inside the same boundary are perceived as a group. Use cards, panels, fieldsets, and table sections purposefully; avoid excessive containers. 9. **Law of Uniform Connectedness** — Visually connected elements are perceived as related. Use shared borders, background, connector lines, or grouping patterns for related steps and relationships. 10. **Law of Prägnanz** — People interpret ambiguous visuals in the simplest possible way. Prefer clear shapes, obvious hierarchy, plain labels, and low-noise layouts. 11. **Law of Continuity** — Users follow smooth visual paths. Align content, preserve reading order, and make steppers, timelines, tables, and lists scan naturally. 12. **Law of Closure** — People mentally complete incomplete shapes or groups. Use carefully designed partial indicators, but never make critical information depend on guesswork. 13. **Law of Common Fate** — Elements moving or changing together are perceived as related. Use coordinated transitions and state changes; avoid unrelated motion. 14. **Law of Experience** — Previous experience shapes perception. Reuse established patterns and domain conventions, especially for navigation, status, editing, filtering, and confirmation. 15. **Von Restorff Effect** — The distinctive item is remembered. Reserve contrast, color, size, and emphasis for the most important action, warning, or status. 16. **Serial Position Effect** — Users tend to remember the first and last items best. Put high-priority navigation and actions at predictable beginning or ending positions. 17. **Peak–End Rule** — People judge an experience largely by its most intense moment and its ending. Make errors, completion, saving, onboarding, and final confirmation especially clear and reassuring. 18. **Goal-Gradient Effect** — Motivation increases as users approach a goal. Show meaningful progress, remaining work, completion counts, and a clear next step. 19. **Zeigarnik Effect** — Unfinished tasks remain mentally active. Make incomplete work visible, resumable, and clearly distinguishable from completed work. 20. **Tesler's Law** — Every system has unavoidable complexity. The product should absorb complexity wherever possible instead of making users understand internal data models. 21. **Occam's Razor** — Prefer the simplest explanation and interaction that solves the problem. Remove duplicate copy, empty states, redundant controls, and unnecessary steps. 22. **Pareto Principle** — A small portion of features often provides most value. Prioritize the core workflow and keep advanced or low-frequency features secondary. 23. **Doherty Threshold** — Productivity improves when the system responds quickly. Provide immediate feedback, loading states, optimistic updates where safe, and avoid unexplained pauses. 24. **Cognitive Load** — Every interface consumes mental effort. Reduce memory demands, use recognition over recall, expose only relevant information, and write concise instructions. 25. **Choice Overload / Paradox of Choice** — Too many options reduce confidence and completion. Use defaults, recommendations, filtering, categories, and progressive disclosure. 26. **Flow** — Users perform best when challenges, feedback, and goals are balanced. Keep users oriented, avoid unnecessary interruptions, and make the next action obvious. 27. **Aesthetic consistency and visual hierarchy** — Although not a single law, these are essential cross-cutting principles: consistent tokens, typography, spacing, color semantics, focus states, and component behavior.
````

### 1.2 What to audit on every page

````text
Check each page for: - **Purpose:** Is the page goal obvious within seconds? - **Hierarchy:** Can users identify the page title, primary action, secondary actions, status, and next step? - **Navigation:** Is the current location clear? Can users move backward, forward, or exit without losing work? - **State:** Are loading, empty, saved, unsaved, error, disabled, selected, and completed states distinguishable? - **Content:** Is information prioritized, concise, scannable, and free of repeated or empty-value noise? - **Interaction:** Are controls discoverable, correctly labeled, reachable, keyboard accessible, and close to the content they affect? - **Feedback:** Does every important action produce timely confirmation or an actionable error? - **Accessibility:** Check contrast, focus visibility, target size, labels, semantic structure, keyboard flow, screen-reader names, error association, and reduced motion. - **Responsive behavior:** Check desktop, tablet, mobile, zoom, long labels, localization, and narrow widths; ensure no clipping, overlap, or horizontal scrolling unless intentional. - **Consistency:** Compare the page with adjacent pages for navigation, button hierarchy, terminology, spacing, forms, status labels, and component behavior.
````

### 1.3 The states to test

````text
Test these states where applicable: - first visit - populated state - empty state - loading state - error state - validation failure - disabled controls - selected and completed states - saved versus unsaved changes - navigation backward and forward - refresh or interrupted flow - keyboard-only navigation - focus visibility - screen-reader semantics - narrow mobile viewport - long content and long labels - zoom or text scaling
````

### 1.4 Special attention

````text
Pay special attention to: - misleading completion indicators - duplicated or low-value information - unclear primary versus secondary actions - controls that are too small or too far from their target content - excessive choices presented at once - unexplained N/A, empty, or technical values - repeated explanatory copy - inconsistent terminology or component behavior - pages that make users manage internal system complexity - missing feedback after save, submit, filter, select, or navigation actions - warnings that do not look meaningfully different from ordinary content - forms without clear labels, units, constraints, defaults, or errors - content that is visually clipped, crowded, or difficult to scan
````

### 1.5 The output structure

````text
Output the audit in this exact structure: # UX Audit Summary - Overall assessment - Strongest patterns - Highest-risk issues - Laws most frequently violated - Accessibility and responsive risk summary # Route-by-route findings For each route: ## [Route name] - User goal: - Primary task: - Overall rating: Strong / Mixed / Weak - Findings table with columns: Law or principle | Status | Evidence | User impact | Recommendation | Severity | Issue type # Cross-page consistency findings Compare navigation, page headers, breadcrumbs or steppers, buttons, forms, cards, tables, filters, status indicators, empty states, errors, save feedback, and responsive behavior. # Prioritized remediation plan Group recommendations into: - P0: blocks task completion, causes data loss, or creates serious accessibility failure - P1: materially increases confusion, errors, or abandonment - P2: reduces efficiency or scanability - P3: visual polish and refinement For each recommendation include: - exact UI change - affected route/component - law or principle improved - acceptance criteria Be rigorous and evidence-based. Do not claim a law is satisfied merely because the page looks clean. Distinguish visual polish from actual usability, accessibility, comprehension, and task success. If a law is not applicable, mark it N/A and explain why.
````

## 2. Reconciliation with the existing corpus

### 2.1 Against `frontend-development-plan.md` §13 ("Laws of UX applied")

The directory **already contains a Laws-of-UX table**, at `frontend-development-plan.md:2479-2519`. It is
not the same list as the canonical checklist and this plan must reconcile the two rather than ship a
second, contradictory table.

- **Four canonical laws are absent from §13:** Law of Continuity (11), Law of Closure (12), Law of Common
  Fate (13), Law of Experience (14). §13's nearest rows are `Law of Uniform Connectedness` (graph/timeline)
  and `Mental Model`; it does not test reading-path continuity, partial-indicator honesty, coordinated
  motion, or cross-product convention reuse as laws.
- **Seven of §13's rows are not in the canonical 27:** Chunking, Mental Model, Paradox of the Active User,
  Parkinson's Law, Selective Attention, Working Memory, Postel's Law. These stay (they are bound to real
  decisions) and are folded in as **adjacent principles**: Chunking→Miller's (5), Working Memory→Cognitive
  Load (24), Selective Attention→Von Restorff (15), Postel's Law→§9 ruling vocabulary (state semantics),
  Mental Model / Paradox of the Active User / Parkinson's Law→kept as design intents that the canonical 27
  do not carry.
- **§13 is a design-intent table; this plan is a verification contract.** §13 says where a law *binds*;
  §3 says how a verdict on it is *produced and evidenced*. The two compose: §13's "Where it binds" column
  is the starting screen list, and §3's instrument column is what turns the binding into a PASS/PARTIAL/FAIL.
- **No row of §13 is deleted by this plan.** Where a §13 row and a canonical law overlap (e.g. §13
  `Miller's Law` ↔ canonical 5), the plan treats §13 as the prior art and cites it, rather than opening a
  competing row.

### 2.2 Against the three existing UI audits (findings already law-shaped)

The audits did not name laws, but many of their findings are law failures in everything but name. The
plan **adopts these as pre-classified**, so the audit re-tests them against the law rather than
re-discovering them. The mapping is one-directional: an existing finding can evidence a law; the law does
not create a new finding.

| Existing finding (source) | Canonical law it evidences | What the plan adds |
| --- | --- | --- |
| X-1 / `F-A1`,`F-B2`,`F-E3`,`F-G7` absence reasons mouse-only (P1) | Accessibility channel; Law of Closure (12) is NOT satisfied by a hidden reason; Cognitive Load (24) | a keyboard-reachability check per absence site, not a `title` grep |
| X-2 / `F-A3`,`F-B6`,`F-C4`,`F-G12` type scale never landed; titles twice | Aesthetic consistency (27); Prägnanz (10); Serial Position (16) | a rendered type-scale read at 390/768/1024/1280 |
| X-3 / `F-F3`,`F-G9`,`F-E7` enamel lost its single home | Von Restorff (15); Aesthetic consistency (27) | count of accented primaries per screen, measured |
| X-4 / `F-D2`,`F-D3`,`F-E2`,`F-E4`,`F-G5` silent writes, double-submit | Doherty Threshold (23); Feedback (1.2); Flow (26) | a pending/disabled state read on each write |
| X-5 / `F-D4`,`F-D22`,`F-E11`,`F-F8`,`F-G4`,`F-G11` mouse-only form semantics | Fitts's Law (3); Proximity (6); Accessibility | the 44px sweep + axe + `aria-describedby` association per field |
| X-6 / `F-G2`,`F-B3`,`F-B7`,`F-D28`,`F-E5` contributor copy | Cognitive Load (24); Occam's Razor (21) | a copy read against the client-vocabulary map |
| X-7 / `F-C1`,`F-G1`,`F-G6` props that match neither child nor server | Prägnanz (10) (a control that renders nothing is ambiguous); Implementation Integrity | the two named TS2339-class escapes, plus the `warning`-token dead-class read |
| X-8 / `F-A2`,`F-F6`,`F-D6`,`F-G12` no ratified measure | Miller's Law (5) line length; Law of Continuity (11) | a measured prose width per cluster |
| `setup-wizard-ui-audit` §2.2 step bar cannot tell saved from unsaved | Zeigarnik (19); Visibility of system status; misleading completion indicator (§1.4) | the live capture now proves it (see §0.5); the plan adds the three-state acceptance |
| `cockpit-ui-audit` §3.2 advisor sticky at `lg` | Fitts's Law (3); Law of Continuity (11) | a measured advisor track width and the `min-w-0` collapse check |
| `remaining-screens` §1.2 one P0 (`F-E1`, Legacy builder data loss) | Peak-End Rule (17) at a destructive confirm; error prevention | kept as a defect, not a design finding (its own note, §3.6) |

### 2.3 Against the app's own UX spine

`design-2.0.md` §2's `State → Options → Recommendation → Explanation → Decision` is the app's intended
decision hierarchy. The canonical laws 17-26 (Peak-End, Goal-Gradient, Zeigarnik, Tesler's, Occam's,
Pareto, Doherty, Cognitive Load, Choice Overload, Flow) are exactly the tests of whether a screen honours
that spine. Where a screen's `Recommendation` is missing or its `Explanation` duplicates, the law row
carries the verdict and the spine names the fix.

### 2.4 Against the instrument findings

`docs/research-scratch/AUDIT-AND-VERIFICATION.md` §"remaining-screens-ui-audit-2026-10-13.md" §4 names six things the quality bar cannot see. Each is a
**blind channel a law travels on**, so this plan adds an instrument for it (§4) rather than repeating the
finding:

| Instrument gap (§4) | Law that travels on it | Instrument this plan adds |
| --- | --- | --- |
| §4.1 typecheck reads no SFC | Prägnanz (10), state semantics | source read + the live capture; do not claim type safety |
| §4.2 copy guard sees no server copy | Cognitive Load (24), Occam's (21) | read the PHP that composes UI strings |
| §4.3 G-18 covers one `ink-muted` pair | Aesthetic consistency (27); contrast | propose pair rows, let the browser gate decide (never move a token) |
| §4.4 registers lag the code | Law of Experience (14) | report the doc/code drift, do not edit the rule |
| §4.5 unvalidated config→badge index | Prägnanz (10) | read the three `DatabaseController` loops |
| §4.6 detector saw nothing | — | treat `[]` as an instrument limit, not a pass |

### 2.5 Against the HLF reference set (the target rendered)

`docs/proposals/HLF_reference/` (25 captures of a v0 build, `v2.0.0`) is the first artifact in the corpus
that **renders** the target rather than describing it, and it is the natural comparison surface for a laws
verdict: a law that FAILs on the shipped screen but PASSes in the HLF names a concrete, already-drawn fix.
Its patterns map to the canonical laws as:

| HLF pattern | Canonical law it satisfies | Note for the audit |
| --- | --- | --- |
| Eyebrow + large title + subtitle page header | Visual hierarchy (27), Prägnanz (10) | the shipped shell `#title` is `text-base` (X-2) |
| Labeled sub-cells (key/value mini-boxes) | Proximity (6), Common Region (8), Prägnanz (10) | absent from the shipped build |
| Status / provenance / category / effect chips | Von Restorff (15), Similarity (7) | the badge colours conflict with `app.css:57` |
| Progress bars + row-count lines | Goal-Gradient (18), Zeigarnik (19) | Careers progress is the clearest gain |
| KPI stat cards + "Next best action" panel | Pareto (22), Serial Position (16), Flow (26) | the shipped Dashboard has no next action |
| Wizard option cards (one Select button + status line) | Hick's (4), Choice Overload (25), Occam's (21) | a **scope** change, not styling (fidelity file §4.3) |
| Preflight single-row warnings, no duplicate Edit | Occam's (21), Flow (26) | matches the audit's own P1 |
| Advisor: Objectives / Priority / Recommended / Recorded | Flow (26), Peak-End (17), Tesler's (20) | the shipped advisor is a flat rail |
| Compare as a property/value table | Continuity (11), Miller's (5) | better for the responsive region contract |
| Honesty notes on raw engine data | Cognitive Load (24), Law of Experience (14) | already the audits' discipline |

Two cautions the audit must carry: the HLF is itself a **wireframe** (placeholder data, invented copy,
unmeasured colours), so it is a comparison target, not evidence of a passing verdict; and it **reproduces
the stepper P0** (all six steps read "done" while every warning says nothing is recorded), so a screen that
matches the HLF is not automatically correct.

### 2.6 Against the colour evidence (`frontend-fidelity-2026-10-13.md` §2.3)

The fidelity file samples the 19 client sheets and the 11 official-site captures and finds the shipped
palette is **already client-measured**, but drops the marketing palette (royal blue `#2255FF`, hot pink
`#FF77CC`) and the client's chrome distribution (fidelity §2.3). Read through the laws:

- **Von Restorff (15) is satisfied today, and a brand palette can break it.** The single lime accent is the
  one distinct element; the audit's instrument (accent count per screen, §3.2 row 15) is the guard. Adding
  blue chrome and a pink call-to-action is a *Track 2 D* contract change (fidelity §6), not a law fix, and it
  must not raise the accented-primary count above one.
- **Aesthetic-Usability (1) is the law the owner's complaint travels on** — but §9 item 13 binds: a richer
  palette is not a PASS by itself and must not mask a usability defect.
- **Law of Experience (14) and Jakob's Law (2) both point at the client.** The Trainer's familiar product is
  the game; a surface that keeps the client's hues but drops its chrome distribution fails the expectation
  those laws describe. This is the same `app.css:57` conflict §2.5 already flags for the badge colours.

A law does not move a token: any colour change goes through the pair table and the G-18 browser gate (§9
item 1, §4.3), and the scope decision is the owner question in `frontend-fidelity-2026-10-13.md` §9 item 9.

### 2.7 Against the game-client screenshot corpus (the domain precedent)

`docs/research-scratch/screenshot-notes/00`-`06`, `screenshot-notes-extras/` and the `screenshot-registry/`
evidence set describe the real client's UI. This matters to two laws above all: **Jakob's Law (2)** and the
**Law of Experience (14)** are measured against *the product the Trainer already uses*, not against generic
web convention, so the client corpus is the audit's primary convention reference. Its conventions map as:

| Client convention | Where | Laws it informs | Note for the audit |
| --- | --- | --- | --- |
| Per-item detail as a modal over a dimmed backdrop | `00:40`, `04:24` | 2 Jakob's, 8 Common Region | a conventional affordance the app does not use |
| Shape-rank vocabulary with no legend (double-circle/circle/triangle/cross) | `00:16`, `00:97` | 7 Similarity, 14 Experience | graded marks read at a glance; the app uses letters |
| One deviant mark carries the worst rating ("the only negative mark in the row") | `00:26` | 15 Von Restorff | |
| Stacked base/bonus gain bubbles | `01:53` | 6 Proximity, 10 Prägnanz | composition encoded by stacking, unlabelled |
| Three-kind log grammar (speech / narration / signed outcome) | `01:143` | 7 Similarity, 8 Common Region, 14 | one list, three entry kinds |
| Conditional risk channel (pink badge only when non-zero) | `01:318` | 15 Von Restorff, 12 Closure | see the warning-role tension below |
| Pending basket distinct from displayed balance | `03:27` | 14 Experience, 27 consistency | selection and commitment modelled apart |
| Stepper's *absence* marks "owned" | `03:50` | 12 Closure, 7 Similarity | absence of a control is the state |
| Goal ladder: one "next" plus a `Goals Achieved 5/8` footer | `02:160`, `02:165` | 18 Goal-Gradient, 19 Zeigarnik | |
| Contextual hint naming the weak stat, on the decision surface | `05:32` | 4 Hick's, 15 Von Restorff, 26 Flow | guidance on the decision, not a help screen |
| Obligation tag pinned to the action button | `05:85` | 9 Uniform Connectedness, 8 Common Region | obligation and action are separate layers |
| Active row breaks an otherwise uniform table (`Current →`) | `06:100` | 15 Von Restorff, 16 Serial Position | |
| One outcome shown as art-anchored prose and a full running log | `06:26` | 17 Peak-End, 20 Tesler's, 14 | summary and auditable record |

Two cautions, and they are why this is a *reference*, not a checklist:

- **The client is not uniformly law-compliant.** It deliberately raises choice load: the Choices panel is
  "a comparison table, not a confirmation dialog: every option's full branch set is listed at once"
  (`02:101`), and the turn is "a six-way choice presented flat" (`05:32`). "The client does it" is not a PASS
  for Hick's Law (4) or Choice Overload (25); the audit must separate a client convention worth copying from
  a client anti-pattern.
- **The client carries alert channels the app cut.** A red exclamation badge (`01:113`), a pink failure
  badge (`01:318`) and a pinned obligation tag (`05:85`) are alert semantics, while §9 item 3 forbids
  inventing a warning role. The conflict is recorded, not resolved (owner question 7).

### 2.8 Against the UX material already in `docs/research-scratch`

Beyond the three audits, the client notes and the HLF, `docs/research-scratch` already carries UX-law and
heuristic material. The plan must reconcile it rather than re-derive it:

| Source | What it is | Canonical laws it touches | How the plan uses it |
| --- | --- | --- | --- |
| `AUDIT-AND-VERIFICATION.md` §3 "UX-law lens (driven, not asserted)" (`:4284-4293`) | the repo's only *driven* law table: Fitts's Law FAILs on nav/export links (L-F01); Hick's, Common Region, Zeigarnik and Von Restorff HOLD; Jakob's/Prägnanz/Aesthetic-Usability "not invoked as anchors" | 3, 4, 8, 19, 15, 2, 10, 1 | prior art and the *method*: a law verdict carries a driven check, not an assertion |
| `UX-DELIVERABLES.md` Part 3 (`:1729-2182`) | a full Nielsen-10 + 5 game-specific heuristic evaluation of the *game*, rated 0-10, severity 0-4, overall 6.6/10 | overlaps 1-7, 10-12, 15, 17-19, 21, 23-27 | the only existing *heuristic* evaluation in the repo; see the two cautions below |
| `DESIGN-CORPUS.md` §5 "Interaction and state patterns worth carrying" (`:4091-4098`) | six declared patterns: preview-before-commit, colour-always-paired-with-a-word, numbers-above-what-they-change, one-saturated-tile-marks-the-active-destination, grouping-by-phase-collapsible, shape-carries-meaning | 3, 6, 8, 9, 12, 15, 18, 24, 27 | the app's own declared principles; a screen that violates one is a law FAIL with a repo citation |
| `DESIGN-CORPUS.md` §35 Progressive Disclosure (`:6503`), §36 Explainability (`:6538`) | declared principles: do not expose every modifier at once; every recommendation must answer "why?" | 4, 20, 21, 24, 25, 26 | the concrete check for the Hick's / Choice Overload / Cognitive Load rows |
| `DESIGN-CORPUS.md` "Additional Design Details" (`:5614-5622`) | shared comparison pattern (never a single unexplained score); `N/A` + `title` for unavailable; progressive disclosure at 320px; non-colour-only warnings; actual-vs-hypothetical balances | 1, 12, 14, 20, 22, 24, 27 | the accessibility floor the laws are tested against; also the source of §9's "no score" rule |

Two cautions:

- **Nielsen's heuristics are not the canonical 27.** The `UX-DELIVERABLES.md` evaluation is organised around
  Nielsen's 10 plus five game-specific heuristics (Feedback Loop Quality, Reward Cadence, Cognitive Load
  Management, Information Architecture, Progressive Disclosure). The plan maps them to the canonical 27
  rather than adopting them as a second checklist — the same treatment §2.1 gives `frontend-development-plan.md`
  §13.
- **That evaluation scores the *game*, not Trainer Desk.** Its ratings and severities are about the client,
  and the whole file is a triaged mirror (`DESIGN-CORPUS.md:3371, 3389-3391`, `D-283`/`D-285`): it supplies
  patterns and a method, never values, and it is not a verdict on this app. Its own provenance is the
  verbatim merge of three source write-ups, tooled at
  `docs/research-scratch-source/scripts/merge_ux_docs.py` and `verify_ux_merge.py`.

### 2.9 Against the 2026-10-13 live-capture laws pass (`UX-AUDIT-REPORT.md`)

A first **partial laws pass** already exists: `docs/audits/UX-AUDIT-REPORT.md` (2026-10-13),
framework "Laws of UX (lawsofux.com)", produced from the same session-private harness this plan adopts in
§4.2 (21 screens × 4 widths, 81 captures). It is the plan's output contract (§1.5, §6) already prototyped:
a summary, per-screen findings tables with a law column and P0-P3 severity, cross-page findings, and a
prioritized remediation plan.

- **Its declared violations:** Visibility of system status, Postel's Law, Occam's Razor, Tesler's Law,
  Zeigarnik, Von Restorff. Note the mix: "Visibility of system status" and "Recognition over recall" are
  Nielsen heuristics, and "Postel's Law" is a §2.1 adjacent principle, not a canonical row.
- **Its declared strengths:** Jakob's Law, Common Region / Proximity, Aesthetic-Usability, Fitts's Law,
  Serial Position.
- **Its findings corroborate the three audits independently** — the stepper "done" P0 (`SetupLayout.vue:45-46`),
  the Preflight duplication, the undifferentiated `N/A`, and the advisor track's missing width floor — a fourth
  confirmation alongside `setup-wizard-ui-audit` §2.2 and the cockpit audit.
- **The cluster-audit rubric already encodes a cognitive-load checklist** (.scratch-uma/ui-audit-2026-10-13/rubric.md:
  single focus, chunking ≤ 4, grouping, one thing at a time, ≤ 4 options at a decision point, no memory bridge,
  progressive disclosure) — the instrument for the Miller / Hick's / Choice Overload / Cognitive Load rows.

**Durability caveat.** This report lives under gitignored `.scratch-uma/` (KI-70), so it is session-private and
**not durable evidence** under §4.6: its findings must be re-derived on a quiet box or promoted into a tracked
document before they count. The plan treats it as a pilot of the output contract, not as a filed audit.

## 3. Coverage matrix — law × cluster × instrument

### 3.1 Clusters (inherited, unchanged)

The eight clusters are `docs/research-scratch/AUDIT-AND-VERIFICATION.md` §"remaining-screens-ui-audit-2026-10-13.md" §0.1 (A shell/home/errors, B reference
catalogs, C Database hub, D1 run lifecycle, D2 queue/settings, E Legacy/Veterans/deck, F career decisions,
G record/outcome/snapshot), plus the two surfaces the other audits own: **Cockpit** (`SCR-CAR-011`) and
**Wizard** (the six `/career/setup/*` steps). Ten units in total. The audit adds no cluster.

### 3.2 The matrix

For each canonical item: the cluster(s) where it is load-bearing, the concrete repo check, and the
instrument that produces the verdict. `Auto` = a gate or script can decide it; `Judge` = expert read
against a screenshot/DOM; `Measure` = a live capture reading (width, height, line count, timing).

| # | Law / principle | Binds (clusters) | Concrete repo check | Instrument |
| --- | --- | --- | --- | --- |
| 1 | Aesthetic-Usability Effect | all | tokens-only, no `dark:`, no shadow/gradient identity (`DESIGN.md:231`); a "clean" screen is not a pass | Judge + token grep |
| 2 | Jakob's Law | A, D1, Wizard | sidebar/table/form/stepper conventions present; `TraineeCombobox.vue` is a real combobox | Judge |
| 3 | Fitts's Law | A, D1, D2, F | 44px house bar; primary action nearest the decision | Auto (`tests/utils/tap-targets.ts`) |
| 4 | Hick's Law | B, C, D1, F, Wizard | simultaneous option count per screen; defaults present | Judge + Measure (control count) |
| 5 | Miller's Law | C, E, F, G, Wizard | chunking to ≤ ~5-7; nav ≤ 8 (`PROCESS-PLANS.md:6560`) | Judge |
| 6 | Law of Proximity | all | label↔field, action↔content distance; provenance badge beside its number | Judge + Measure |
| 7 | Law of Similarity | B, C, E, F | equivalent controls share one component/class; `VeteranRow` is the one row shape; one `SparkChip` per spark (`PROCESS-PLANS.md:6686`) | Auto (class grep) + Judge |
| 8 | Law of Common Region | all | cards/fieldsets bound one concept; no over-containerisation | Judge |
| 9 | Law of Uniform Connectedness | E, G | ancestry graph lines, timeline rail, stepper connector | Judge |
| 10 | Law of Prägnanz | all | one idea per card; no ambiguous control; dead `warning` classes (`F-G1`) render nothing | Judge + source |
| 11 | Law of Continuity | A, C, F, G | reading order; stepper/timeline/table scan path; `lg` track balance | Judge + Measure |
| 12 | Law of Closure | A, B, G | partial indicators honest; a hidden reason is a FAIL, not closure | Judge + a11y |
| 13 | Law of Common Fate | F, G | one `animate-pulse` (`F-G10`); coordinated transitions only | Auto (motion grep) |
| 14 | Law of Experience | all | reuses domain conventions; docs/code drift reported (§4.4) | Judge |
| 15 | Von Restorff Effect | A, D2, F, G | accented primaries per screen; the one `RECOMMENDED` marker | Measure (count) |
| 16 | Serial Position Effect | A, F, Wizard | primary action first/last; high-priority nav at ends | Judge |
| 17 | Peak-End Rule | G, Wizard, Result | errors/completion/save/final-confirm are clear; `F-E1` destructive confirm | Judge + source |
| 18 | Goal-Gradient Effect | F, Cockpit, targets | progress shows distance-to-goal, not just current value | Judge |
| 19 | Zeigarnik Effect | Wizard, E, F | incomplete work visible/resumable/distinct; step bar three-state | Judge + Measure (stepper) |
| 20 | Tesler's Law | Wizard, E, F | the tool absorbs complexity; no internal-model exposure | Judge |
| 21 | Occam's Razor | all | duplicate copy/controls/steps removed; deck paragraph ×6 | Judge + source |
| 22 | Pareto Principle | A, D1, Wizard | core workflow first; advanced features secondary | Judge |
| 23 | Doherty Threshold | B, D1, D2 | immediate feedback; `processing` exists; no unexplained pause | Measure (timing) + source |
| 24 | Cognitive Load | all | recognition over recall; concise instructions; no contributor copy | Judge + source |
| 25 | Choice Overload | B, C, Wizard | defaults/recommendations/filtering/progressive disclosure | Judge |
| 26 | Flow | Cockpit, F, Wizard | oriented, uninterrupted, next action obvious; no modal over a decision | Judge |
| 27 | Aesthetic consistency & hierarchy | all | one token set, one type scale, one focus ring, one component behaviour | Auto (token grep, G-18) + Measure |

### 3.3 Automatable vs judgement

- **Automatable now:** 3 (tap-targets), 13 (motion grep), 27 (token/type grep + G-18 browser gate), and the
  accessibility sub-checks under 1.2's Accessibility row (axe, `aria-describedby` association).
- **Measure (live capture):** 4, 6, 11, 15, 19, 23 — width, control count, accent count, stepper state,
  timing. These are the rows `docs/research-scratch/AUDIT-AND-VERIFICATION.md` §"remaining-screens-ui-audit-2026-10-13.md" marks `needs measurement`.
- **Judgement only:** 1, 2, 8, 9, 14, 16, 17, 18, 20, 21, 22, 24, 25, 26. Each must cite a screenshot or a
  `file:line`, never an impression.

## 4. Instruments

### 4.1 Source read (primary, cheapest)

Read each page and its composed components plus the controller that builds its props, as the existing
audits did. Every claim about rendered appearance that the source cannot settle is marked
`needs measurement` and names the measurement (the discipline `docs/research-scratch/AUDIT-AND-VERIFICATION.md` §"remaining-screens-ui-audit-2026-10-13.md"
states at its head, `:14-18`).

### 4.2 Live browser capture (the measurement instrument)

The 2026-10-13 session established the harness and it is reusable:

- **Session-private, always** (`AGENTS.md` §9, KI-73, KI-80): a private port and a private scratch
  database, e.g. `PLAYWRIGHT_PORT=8177 PLAYWRIGHT_SCRATCH_DB=.scratch-uma/ux-audit/browser-8177.sqlite`.
  An unqualified `npm run test:browser` in a shared worktree takes a peer's suite down.
- **Pre-flight every broad sweep** (`KI-74`): time one trivial SQLite read against the dev file; if it
  takes more than a few seconds the box is contended and the sweep is **void before it starts**. Measured
  2026-10-13: 0.93s quiet (`docs/research-scratch/PROCESS-PLANS.md` §"trainer-desk-remaining-baseline.txt" (source line 17)), 6ms on a second quiet read, but a
  later run was cut short by peer Pest suites and two dev servers (8143, 8000) and was **not** relaunched.
- **Capture contract per (route × width):** full-page screenshot at 390/768/1024/1280, plus a metric pass
  — horizontal overflow (`scrollWidth - clientWidth`), elements where `scrollWidth > clientWidth`, the
  minimum visible control height, and a "squeeze" scan (a short leaf text node wrapped to ≥3 lines, the
  proxy for the "one character per column" defect class).
- **Resumable and incremental:** write results after every capture and skip a `(label,width)` already in
  `results.json`, so a crash or a contended box loses nothing. The 2026-10-13 first attempt lost its
  metrics because it wrote only at the end; the rewrite fixed that.
- **Axe as a separate pass** (`AUDIT_AXE=1`), never inside the timing sweep, so the layout pass stays
  fast and hang-resistant.

### 4.3 Gates reused (not reinvented)

`tests/browser/accessibility.spec.ts` (axe A+AA scoped to `#app`), `tests/utils/tap-targets.ts` (44px),
`tests/utils/accessibility.ts` (`buildAxe`), `tests/Feature/DesignTokensTest.php` (the G-18 pair list at
`:298`, `:540-546`), `tests/Feature/RenderedCopyHygieneTest.php` (with its §4.2 limit), and the browser
specs named per screen in the three audits' "Guarded" columns.

### 4.4 Measurement discipline

A verdict is `Measure` only when a number was read. Contrast is never asserted from a class list; the
G-18 pair list is extended and the browser gate decides (§4.3, `remaining-screens` §5.1). A passing axe
scan is never proof of an accessible name (`remaining-screens` §5.11, `F-G4`).

### 4.5 Reference-target comparison (the HLF)

For any law that FAILs on a shipped screen, compare it against the matching capture in
`docs/proposals/HLF_reference/` before writing the recommendation (§2.5): a pattern the HLF already renders
(sub-cells, badges, progress bars, the option-card wizard) turns a judgement call into "adopt this pattern,
subject to the token and scope decisions in `frontend-fidelity-2026-10-13.md` §4-§6". This is a `Judge`
instrument with a rendered citation, not a `Measure` one, and the HLF's own caveats (§2.5) apply.

### 4.6 Evidence discipline borrowed from the screenshot registry

The registry is a worked example of the evidence contract this audit needs, and its rules are stricter than
"cite a file":

- **A note is a lead, not a verification.** `screenshot-registry/README.md:166-167`; the state
  `documented_unverified` exists to mark that boundary. A law verdict is never based on a note alone.
- **No rule claim from a single frame.** `screenshot-registry/FINAL_REPORT.md:277`: "No claim about a rule
  has been made from a single screenshot." A single frame may evidence a *state*, not a *rule*.
- **Confidence is state-encoded, not prose.** `verified` requires a recorded observation; `partial` requires
  an outstanding question (`README.md:92-93`). A law verdict carries the same: PASS with its evidence, or
  PARTIAL naming what is unresolved.
- **Provenance is append-only** (`README.md:112`), and a cluster "is not a byte identity" (`README.md:61`,
  `FINAL_REPORT.md:276`). Where a finding rests on a perceptual group or a derived index, the caveat travels
  with it.
- **Inference is fenced from observation.** `PHASE-B-REPORT.md:154` flags a note's "presumably" as "an
  inference marker in a note that elsewhere separates observation from reading". A verdict that reasons past
  what a frame shows must say so.

Applied here: every PASS/PARTIAL/FAIL in the §6 report carries a screenshot path or a `file:line`; a measured
claim names the number (§4.4); and a claim resting on one frame is downgraded to `needs measurement`.

## 5. Method per page

For each route:

1. State the page's primary user goal and the main task.
2. Walk §1.2's ten axes and §1.3's states; note which states the page cannot reach.
3. Score each **applicable** canonical law PASS / PARTIAL / FAIL / N/A; N/A names why (e.g. Goal-Gradient
   is N/A on a static reference table; Common Fate is N/A on a screen with no motion).
4. Cite concrete evidence: a `file:line`, a screenshot path, or a measured number. No claim without one.
5. State the user impact in UX terms (discoverability, comprehension, decision cost, error risk,
   confidence, efficiency, accessibility, completion).
6. Give one specific recommended change, never a vague suggestion.
7. Assign severity P0-P3 (§7) and an issue type (visual, content, interaction, responsive, accessibility,
   architectural).
8. Reconcile with the existing audit: if the finding is already in one of the three audits, cite its ID
   (`F-xx`) instead of filing a new one; if it is new, it is new evidence, not a new register entry.

## 6. Output contract

The report is produced in the exact structure of §1.5, with one addition that the existing corpus needs:
each route's findings table carries an **`Existing ID`** column so a reader can join a law verdict to the
audit finding it came from (`F-A1`, `X-1`, …) and never sees the same defect filed twice. The four report
sections map to the existing files as: **Summary** ≈ `remaining-screens` §1 (score card, reused) plus a
laws-frequency count; **Route-by-route** ≈ the cluster tables (§3) re-projected per law; **Cross-page
consistency** ≈ `remaining-screens` §2 (X-1..X-8) re-read as law rows; **Prioritized remediation** ≈ the
existing severities, grouped P0-P3, each with an acceptance criterion.

## 7. Severity and scoring

Severity is the impeccable scale already in use (`docs/research-scratch/AUDIT-AND-VERIFICATION.md` §"remaining-screens-ui-audit-2026-10-13.md" §1.2): P0
blocks task completion, causes data loss, or is a serious accessibility failure; P1 materially increases
confusion, errors, or abandonment; P2 reduces efficiency or scanability; P3 is polish. The plan adds no
new scale. A law FAIL is not automatically P0: severity is the impact, not the law. The cluster score card
(§1.1) is **not** rescored; the laws layer reports counts per law, joined to the existing severity counts.

## 8. Effort and batching

- Ten clusters, ~50 routes, four widths. Capture dominates wall-clock (a document is ~0.3-1s plus a
  screenshot; the 2026-10-13 sweep averaged roughly 0.3 shots/s before contention).
- Batch one cluster at a time, in the existing order (A → G, plus Cockpit and Wizard), each batch =
  capture → per-law verdicts → reconcile with the existing IDs. Two checkpoints (after D2, after G) so
  emphasis can be redirected before the tail.
- The sweep is split into a **layout pass** (no axe, fast, resumable) and an **axe pass** (subset), so
  neither can void the other under load.

## 9. What this audit must not do

Inherited verbatim in substance from `docs/research-scratch/AUDIT-AND-VERIFICATION.md` §"remaining-screens-ui-audit-2026-10-13.md" §5, because a law is not a
licence to break a ruling:

1. Do not move a colour token to answer a contrast question; propose pair rows and let the gate decide.
2. Do not add a fifth provenance label or a section-qualifying badge.
3. Do not invent a `--color-warning` role; the declared pair is `risk`.
4. Do not reflow the race calendar, turn log, or compare tables into stacked cards; the region contract is
   `overflow-x-auto` + `role="region"` + `tabindex="0"` + `aria-label`.
5. Do not add a cold-load spinner, skeleton, or `aria-busy` on page load (`ADR-0007`).
6. Do not add a score, percentage, confidence, risk grade, win chance, or recommendation to any surface.
7. Do not name a scenario in a component (gate G-33) or default a `scenarioLabel`.
8. Do not restyle per-page headings before the shell `#title` decision is settled.
9. Do not migrate an absence without moving the specs that pin the current channel (owner approval).
10. Do not add a client-side theme write.
11. Do not treat a passing axe scan as proof of an accessible name.
12. Do not widen `AGENTS.md` §7's component list, and do not hand-edit `PRODUCT.md`, `SKILL.md`, or the
    derived ADR index.

And two the laws lens specifically tempts, added here:

13. Do not treat "it looks clean" as a PASS. §1.5's closing line binds the auditor: polish is not
    usability, accessibility, comprehension, or task success.
14. Do not let a law override a ruling. Where a canonical law asks for what an ADR or a `SCREEN_SPEC`
    ruling forbids (no score, no win-chance, no warning role, no scenario name in a component), the
    conflict is recorded and the ruling wins.

## 10. Questions for the owner

1. **One table or two (§2.1)?** `frontend-development-plan.md` §13 and this plan both map laws to
   decisions. Should §13 be restated to the canonical 27 and the seven extras folded in as adjacent
   principles, or should §13 stand as design-intent and this plan stay the verification contract beside
   it? (The plan currently assumes the latter.)
2. **The four laws §13 omits (Continuity, Closure, Common Fate, Experience).** Are they in scope as
   first-class rows (the plan assumes yes), or deliberately traded as §13's "Deliberately traded" note
   does for Occam's Razor in the Database area?
3. **Zeigarnik and the step bar.** The live capture proves the bar marks visited-not-saved steps "done".
   Does the fix change the label (keep the 2026-10-13 ruling that "completed" means visited) or the state
   model (three states, "done" only when saved)? This is the same question `setup-wizard-ui-audit` §6
   raises; it needs one answer.
4. **`Measure` budget.** Do you want the full four-width capture on all ~50 routes, or the reduced
   first pass (one representative per cluster) that the 2026-10-13 live run effectively produced before
   contention?
5. **Register (§2.2, §4.4).** A law audit will surface the same defects the three audits already filed.
   Confirm the rule the plan assumes: cite the existing `F-xx`/`X-n` and add no new register entry unless
   the defect is genuinely new.
6. **The HLF as the comparison surface (§2.5, §4.5).** Should every FAIL be compared against the matching
   `HLF_reference` capture before a recommendation is written — i.e. is the HLF the canonical comparison
   target? And does the HLF's wizard simplification (option-card presets that drop the per-stat, ancestry
   and deck editors) fall inside the audit's scope, or stay a separate `SCREEN_SPEC`/`PRD` scope decision
   as `frontend-fidelity-2026-10-13.md` §4.3 proposes?
7. **The client's alert channels (§2.7).** The client carries a red exclamation badge (`01:113`), a pink
   failure badge (`01:318`) and a pinned obligation tag (`05:85`), while §9 item 3 forbids inventing a
   `--color-warning` role and the declared pair is `risk`. Does the audit record the client's alert
   vocabulary as a convention to *report* (a PARTIAL on Von Restorff/Closure), or does it stay out of scope
   because the app's cut is deliberate?
8. **Method of record (§2.8).** The repo already holds a Nielsen-style heuristic evaluation of the game
   (`UX-DELIVERABLES.md` Part 3: 0-10 ratings, 0-4 severity, an overall 6.6/10). Should this audit adopt that
   rating *shape* (a per-heuristic score and an overall number) alongside the PASS/PARTIAL/FAIL contract and
   the impeccable severity scale, or keep the two separate — the laws layer reporting counts per law, as §7
   assumes?

## 11. Verification plan for any approved subset

Per `AGENTS.md` §9's cost-aware escalation. Nothing below was run for this plan.

| Approved change | Level 1 while working | Level 2 at slice end | Level 3 before hand-off |
| --- | --- | --- | --- |
| This plan itself (docs only) | `python tools/md_fence_check.py docs/proposals/ux-laws-audit-plan-2026-10-13.md` | — | the file's own fence check; no code gate applies to a proposal |
| A law verdict re-classified against an existing finding | the named finding's Guarded tests | the cluster's specs | the hand-off sequence only if the re-classification changes a proposed fix |
| Any fix a law row recommends | that screen's spec + `tests/utils/tap-targets.ts` where height is involved | `tests/browser/accessibility.spec.ts` | the full suite; a `Measure` claim must carry the number, not a screenshot alone |
| The live capture harness (§4.2) | one cluster at one width | the cluster at four widths | pre-flight the SQLite read (`KI-74`); if contended, the sweep is void and is reported void |

## 12. Hand-off notes

- **Deliverables produced:** this file. No component, token, copy string, gate, spec, or test was changed.
- **Facts this plan relies on, and their source:** the canonical 27 come from the supplied checklist (§1);
  the existing Laws table is `frontend-development-plan.md:2479-2519`; the findings and severities are
  `docs/research-scratch/AUDIT-AND-VERIFICATION.md` §"remaining-screens-ui-audit-2026-10-13.md" §1-§3; the instrument limits are its §4; the two other
  audits own the Cockpit and the wizard; the baseline is `docs/research-scratch/PROCESS-PLANS.md` §"trainer-desk-remaining-baseline.txt"; the live
  capture harness and the 81 screenshots are `.scratch-uma/ux-audit/`; the rendered comparison target is
  the 25 captures in `docs/proposals/HLF_reference/` (§2.5), read in full; the colour evidence in §2.6 is
  sampled in `frontend-fidelity-2026-10-13.md` §2.3 (quantized pixel buckets from the 19 client sheets and
  11 site captures, via `.scratch-uma/ux-audit/palette-extract.ps1` and `palette-accents.ps1`); the
  game-client conventions in §2.7 are from `docs/research-scratch/screenshot-notes/00`-`06`,
  `screenshot-notes-extras/` and `screenshot-registry/` (all citations verified against source), and §4.6's
  evidence rules are `README.md` and `FINAL_REPORT.md`; the prior UX-law and heuristic
  material reconciled in §2.8 is `AUDIT-AND-VERIFICATION.md:4284-4293` (the only driven law table),
  `UX-DELIVERABLES.md:1729-2182` (the Nielsen-style heuristic evaluation) and `DESIGN-CORPUS.md:4091-4098,
  5614-5622, 6503, 6538`. `docs/research-scratch-source/` was scanned and holds **no** UX-law material (0
  markdown files; raw scrapes, game JSON, a screenshot inventory and build scripts), so nothing was drawn
  from it beyond the merge-tool provenance of `UX-DELIVERABLES.md`; `docs/audits/UX-AUDIT-REPORT.md`
  (2026-10-13) is a first partial laws pass, reconciled in §2.9 with a durability caveat, and
  .scratch-uma/ui-audit-2026-10-13/rubric.md supplies the cluster-audit cognitive-load checklist.
- **Not delivered, stated plainly:** no law verdict was produced here; this is the plan that says how to
  produce them. No browser measurement, no contrast arithmetic, no suite run, no lore gate. The HLF was
  read as reference; nothing in it was implemented or copied.
- **Facts this pass corrected in its own brief:** the directory already carries a Laws-of-UX table (§13 of
  the frontend plan) and it is not the canonical 27 — four canonical laws are missing from it and seven of
  its rows are not canonical; the live capture corroborates two existing findings (the step bar and
  Preflight) rather than opening new ones; and the HLF reference set now supplies a rendered target, which
  makes the §4.5 comparison instrument possible. **Added 2026-10-13:** the game-client screenshot corpus is
  the primary convention reference for Jakob's Law and the Law of Experience, so §2.7 maps it and §4.6 binds
  the audit's evidence discipline to the registry's rules.
