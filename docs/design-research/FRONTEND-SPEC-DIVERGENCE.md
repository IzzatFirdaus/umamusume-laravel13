# Frontend spec — divergence audit

A pasted synthesis titled *"Umamusume Trainer Companion — Frontend & User Flow"* describes this app
as the product of the design corpus. Audited 2026-09-27 against the shipped CSS, `DESIGN.md`,
`CONSTRAINTS.md`, `PRD.md`, and the scenario data.

**Disposition: unmerged, per D-283.** It is a better summary of the corpus than most of the corpus,
and it is wrong in eight specific places, three of which would undo rulings that measurement or the
owner already settled. It also exposes two real gaps in our own docs, recorded at the end.

The document describes **our tool**, not the client, so unlike the two incoming write-ups this one is
checkable line by line against code. Every verdict below cites a file and line.

---

## 1. Where it disagrees with the repo

### 1.1 Theme default is inverted — owner ruling, not a preference (blocking)

The document states **"Light is default. Dark is supported."** Standing decisions say the opposite:

- `DESIGN.md:5` (root) records the closed identity ruling as **"dark-first theme, tactical-athletic
  identity, catalog-first"**.
- `resources/css/app.css:33` sets `--color-page: #0D0C0F` at the root, with the light values confined
  to `html[data-theme='light']` at line 120. The shipped app is dark-first today.
- The research package's light-first reading (`RAW-FINDINGS.md:47`, "a dark-mode-first design would
  be unrecognisable as this game") is the *other* side of the unresolved identity tension, not a
  third opinion. It is the argument the owner ruled against.

Adopting this line means flipping the theme and rewriting `app.css`'s token layer. **Escalated, not
decided here.** Until it is ruled, the research-vs-Trainer-Desk identity conflict stays the single
largest open item in the corpus, and this document is the third artifact to take the light side by
default rather than by argument.

### 1.2 The token table gives one hex per role, and one hex is a rejected value

| Document | Shipped | Status |
|---|---|---|
| Decrease/loss `#0088E0` | `--color-down: #4EA1E8` (dark), `#0667B0` (light) | `#0088E0` is the **client's measured blue**, deliberately diverged from: `app.css:149` records it measuring **2.12:1** on the light field. Quoting it as the tool's token reintroduces a contrast failure this repo caught by measurement |
| Increase/gain `#FF9A2C` | `#FF9A2C` dark, **`#B45309` light** | Right value, wrong completeness — see below |
| Page field `#F2F1F8`, ink `#6A5641`, headings `#482720`, green `#7FCC09`/`#4E7906`, gold `#EFC96A`, crimson `#800014`, indigo `#351F70` | all present in `app.css` | **Correct** |

The structural defect is that the table lists **one hex per role** while the actual system is
theme-split. That is D-258's exact failure — "a contrast rule must name its second colour" — and
G-18 requires every pair to pass in both themes. A row reading `#FF9A2C` is safe in dark and 2.12:1
on white. The table needs a light column and a dark column, or it needs to reference tokens rather
than values (G-4).

### 1.3 `~70+ turns` — the banned denominator, third appearance

Flow 2 is headed **"Log a Turn (repeated ~70+ times)"**. D-136 forbids exactly this and G-31 greps
for it: no source in this repository records a total turn count. The same family has now produced
"~50 rounds" (withdrawn), "70+ turns" (rejected in `MECHANICS-TRANSLATION-TRIAGE.md:83`),
`Turn 24/72` (rejected in §7.2 of that file), and this. It survives in prose that is otherwise
careful, which suggests the figure feels like scene-setting rather than a claim — it is still the
value an implementer would reach for when a progress bar needs a denominator.

### 1.4 Legacy step uses the copy the ruling bans

Flow 1 Step 3 reads **"Select Legacies (Inheritance)"** with **"Two parent slots, each bringing two
grandparents."** Three separate rulings bear on it:

- The owner's standing instruction: **"Legacy Select", not "Inheritance Screen"**, and Legacy /
  Ancestor for the nouns (D-267).
- `UMAMUSUME_REFERENCE.md:571`: `[Global]` localises the **system** as **Inspiration**, the chosen
  ancestors as **legacies**, the traits as **Sparks**.
- `CONSTRAINTS.md` §4 now carries all three rows, added this week.

"parent"/"grandparent" in UI copy fails the lore-and-terminology gate; `(Inheritance)` as a gloss
contradicts both the owner's label and the client's. The underlying structure the document describes
— two slots, each with two of their own, one guest rental, affinity as a header pill — is all
**confirmed** against §6.27 and the frames. Only the vocabulary is wrong.

### 1.5 "Screen E — Race Calendar" collides with existing artifacts

There is no Screen E in the architecture section, and the letter is already used. `DESIGN.md` §8
defines **A–D only** (`8.1`–`8.4`), then continues with event and race *subsections* that carry no
letter. On disk:

- `mockups/screen-e-run-completion-v1.png` — the Run Completion surface from §6.28 / §10r.
- `mockups/screen-e-run-list-v1.png` — an earlier Run List frame, since superseded by §8.3.
- `prototypes/superseded/screen-e-scenario-switch.html` — a scenario-header variant.
- `mockups/screen-f-legacy-select-v1.png`, `screen-f-legacy-second-generation-v2.png` — Legacy Select.
- `mockups/screen-f-skill-search-v1.png` — which collides with §8.4's **Screen D**.

So "E" and "F" are already overloaded, and this document adds a fourth meaning to E. Race Calendar is
**a panel**, specified at §6.20 and switched off per-scenario by D-221/G-34 — promoting it to a screen
letter is what created the collision. Needs a register (§1.2 below), not another letter.

### 1.6 Nunito is presented as settled; it is parked behind C-8

"Mandatory. Recommended: **Nunito**" and "Banned: … system-ui as primary" both overstate a live
decision. `DESIGN.md` §4.1 does mandate a rounded humanist sans and does ban neutral grotesques as
the primary voice — so the *principle* is faithfully quoted. What is not settled is the typeface:
`app.css:28` carries the comment **"The rounded display-font proposal stays parked behind a C-8
approval"**, and the shipped stack is `ui-sans-serif, system-ui, sans-serif`. Root `CONSTRAINTS.md`
C-8 bars adding a dependency without approval, and "no new dependencies" was the standing instruction
for the Laravel phase. `KNOWN-ISSUES.md` KI-3 records the related offline break in
`welcome.blade.php`.

Consequence worth naming plainly: **as shipped, G-21 (font identity) fails**, because
`--font-sans` resolves to a system stack and §4.1 bans that as the primary voice. The document's
ban on `system-ui` is technically correct against §4.1 and lands on our own code. Either the font is
approved and bundled, or G-21 needs an explicit exemption note in `KNOWN-ISSUES.md`. This is an owner
decision, not a docs fix.

### 1.7 `Energy +5` on Wit training is the rejected figure re-imported

Screen B's preview row reads **"Wit Training (+9, Speed +2, SP +4, Energy +5)"**, and the advisory
says **"Wit costs 0"**. `MECHANICS-TRANSLATION-TRIAGE.md:52` already rejected the `+5`:
`UMAMUSUME_REFERENCE.md:97` records Wit recovery as *"a small amount"* with no figure, and the +5 in
our data belongs to a **Unity Cup Spirit Burst** bonus, a different mechanic. "Costs 0" is a further
step beyond the source: recovering some energy is not costing none. Both read as invented constants in
the one component whose purpose (P2, D-256) is to show only sourced numbers before a commitment.

### 1.8 Two caveats dropped

- **"Danger" band below 30** appears without §6.15's own annotation that the 30 boundary is **an owner
  ruling, not a game fact** — no source publishes a threshold below 50 (`ADR-0001` §3). Dropping the
  caveat turns a house rule into a claim about the game, which is what D-256 and G-46 exist to prevent.
- **Support Card Rail "(if any)"** on the dashboard is quoted faithfully from §6.5b, but §6.5b itself
  sits against `PRD.md` §6.9, which excludes a support-card database from Phase 1, and
  `docs/adr/0005-support-card-entities.md:3`, whose status is **"PROPOSED. Not accepted, not built."**
  The rail cannot be populated from data the tool is not allowed to hold. This is a tension **inside
  our own corpus** that the document inherited rather than introduced — recorded below as gap 2.

---

## 2. Where it is right, and useful

Accurate against the corpus and stated with the correct reasoning: the is/is-not framing tied to
Planner Rules 1–6 and PRD §6.11; shape-over-shadow, no blur/glass/neumorphism; the ornament budget of
two motifs per region with the "no character model to fill the middle" argument; inverted deltas as
"the most copyable-to-wrong thing"; the stat band's six columns with tints rather than saturated hues
(D-114); grade badges; the torn-page turn chip first in DOM order; preview-before-commit with
selecting never writing (P2); no numbered step sidebar (D-117); `1–5` for the five disciplines, Enter,
Escape (D-55, `DESIGN.md:557`); append-last timeline; fan readout in client sentence form with no
progress bar; Trackblazer with no calendar and the Race Fatigue chip vanishing after Late December
(D-230/D-231); Unity Cup burst states where "spent is never inert" (G-36); no Energy penalty on
Special Training post-rework; Run Completion with Career Rank and Rating **entered, never derived**
(D-270), Major Wins from logged races (D-271), fan class Bronze→Legend (D-272); affinity as a header
pill; the micro-grade strip as `aria-hidden` with values retained on the card (D-115); count-up at
240ms (`DESIGN.md:1382`); the "What Must NOT Appear" table, which cites PRD §6.1/§6.6/§6.9/§6.11/
§6.12/§6.13, NFR-6, D-20, Planner Rule 5 and D-114 correctly.

Two framings are better than what our docs currently say and are worth adopting:

- **"The preview is the input"** — already in §8.2, but this document's sentence
  *"they are picking the thing that produced the numbers they can see"* is the clearest statement of
  why Screen B is not a form.
- **"Adding Unity Cup or Trackblazer chrome to a URA run is a factual error, not a design choice."**
  That is D-220's rule, and this wording is more actionable than ours. It also generalises to the
  undescribed fourth scenario, which is what G-41 tests.

---

## 3. Two gaps in our own corpus that this document exposed

1. **No screen-letter register.** §8 names A–D; E and F exist only in mockup filenames and have been
   reused at least four times (Run Completion, Run List, Scenario Switch, and Skill Search under F
   while D is its §8 letter). Any new artifact will keep guessing. Fix: an explicit table mapping
   letter → surface → owning section → artifact, with panels (Race Calendar) excluded from letters.
2. **§6.5b requires a Support Card Rail while §6.9 bars the data for it.** The rail is specified as
   persistent dashboard content, and PRD §6.9 excludes a support-card database from Phase 1, with
   ADR-0005 still PROPOSED. One of the two needs to yield, and it is a scope question for the owner.

---

## 4. Actions

- **Nothing merged.** This document stays in chat as a description of intent; the corpus remains the
  source of truth, and §1.1–§1.8 above are the delta.
- **Escalated for owner ruling:** theme default (dark-first vs light-first), font approval under C-8
  and the resulting G-21 failure, and the support-card rail versus PRD §6.9.
- **Corpus fixes proposed, not yet applied:** the screen-letter register (§3.1), a light/dark column
  on any token table that claims to be normative, and an explicit note at §6.15 that 30 is ours.
- **Rejected outright:** the `70+` denominator, `Energy +5` / "costs 0" for Wit, `#0088E0` as a tool
  token, `parent`/`grandparent`/`(Inheritance)` in copy, and Screen E reassigned to Race Calendar.
- No production code changed in the audit pass. The `+0 breakthrough` string flagged in the previous round
  is still live at `resources/views/components/stat-band.blade.php:153` awaiting a decision.

---

## 5. Rulings received 2026-09-27 and what they changed

Three escalations came back as owner decisions, so this section records the outcome rather than leaving
§1.1/§1.6/§1.8 describing them as open.

- **Theme: light-first, the document's way.** `resources/css/app.css` was inverted — the measured light
  system now lives in the default block and the raceboard anchors in `html[data-theme='dark']`. Root
  `DESIGN.md` §2.1 and §9 carry the reversal, and research §11 item 10 is narrowed to the three axes still
  contested (radius, elevation, ornament dose). **The document's colour table was still not adopted**: it
  lists one hex per role, and the flip made both values necessary, so §2.1 now pairs them.
- **Font: system stack kept, §4.1 mandate exempted.** Recorded as `KNOWN-ISSUES.md` KI-6, including the
  warning that an exempted gate is invisible to the reviewer who checks it. The pasted spec's "Rounded
  humanist sans — mandatory, Nunito recommended" is faithful to §4.1 and wrong about what is approved.
- **Support card rail: renders from entered turns only.** `DESIGN.md` §6.5b now states the data source,
  the manual label, and the ban on effect magnitudes — because §6.9 and ADR-0005 leave no card table to
  fill it from.

**Two things the flip surfaced that the audit had not found.**

1. **`--color-sp` could not carry text in the default theme.** It measured 2.66-2.98 on the three light
   surfaces — and the research package already said so at §3.4 while §3.3 mandated the same value as the
   Skill Point identity. The contradiction was live in the corpus, not introduced here. Resolved by
   splitting the token the way green/chrome are split: `--color-sp` keeps the client cyan for fills and
   rules, `--color-sp-ink` (`#0E7490`, cyan-700) carries text at 4.77 / 5.05 / 5.36.
2. **Tailwind prunes unreferenced `@theme` tokens.** `--color-sp-ink` was emitted only in the dark
   override, so in light `var()` resolved to nothing and the probe silently inherited the body ink —
   reporting **6.56:1 for a token that was absent**. An undefined variable cannot fail a contrast test;
   it passes with someone else's colour. Fixed by declaring `@theme static` (+0.03 kB), and the measuring
   rule is now `CONSTRAINTS.md` **D-288**: read the resolved custom property, never sample through an
   element that already has a colour, and treat empty as a failure.

**Verification after the flip.** All 41 tokens resolve in both themes, and 19 text/background pairs plus
the 9 grade-badge fills clear 4.5:1 in both. Worst light pair `up/panel` 4.74; worst dark `down/raised`
5.48; lowest grade letter contrast 9.00 (dark `SS`) and 9.19 (light `E`). `npx vite build` clean.

**Still open, and both are production code under the phase fence:**

- `resources/views/components/stat-band.blade.php:153` renders `+ 0 breakthrough`, asserting an
  untracked zero.
- `config/scenarios.php:50-54` ships `step => 150` with nine integer labels, while `DESIGN.md` §6.7 and
  D-274 describe the 50-point half-step ladder the client frames actually show. The component computes
  `intdiv(value, 150)`, so it cannot emit `D+` or `E+`, and line 160 prints "Grade banding is every 150
  points" to the Trainer — publishing a rule this corpus already falsified. That is a disclosed-derivation
  breach (D-256, G-46), not just a stale constant, and `KNOWN-ISSUES.md`'s related-issues bullet still
  describes the 150 model as the implementation without noting the conflict.


---

## 6. Second batch: scenario flows and UX behaviour (2026-09-27)

Two more app-facing documents arrived: `docs/Scenario-Specific User Flows & Frontend Specifications.md`
(1,167 lines, Unity Cup + Trackblazer flows and 15 component specs) and
`docs/UX Behavior Specification - Umamusume Trainer Companion.md` (569 lines, global behaviour, an
input taxonomy, a run-lifecycle state machine, and state/motion/accessibility contracts).

**These are the best-grounded incoming artifacts of the session.** The rejection list from the previous
two rounds largely did *not* reappear: Trackblazer caps are in the **corrected** order
(`flows:466` = `1200/1900/1200/1200/1500`, matching `config/scenarios.php:114`, not the transposed row),
no total-turn denominator appears anywhere, the energy bands reproduce `DESIGN.md:824-834` **including
the "30 is an owner ruling, not a game fact" caveat**, `Energy Drink MAX EX` is 50 coins not 55, the shop
facility raise is "permanent for the run", the grade-objective variants (60/+300/+300, dirt 30/200/300)
match config, and the motion table copies `DESIGN.md:1387-1395` with no invented timings. Both documents
also handle the fourth scenario honestly: `flows:1086` and `ux:150` render Our Grand Concert as
baseline-plus-known-caps under D-241 rather than inventing chrome for it.

### 6.1 Errors worth recording

| Where | Problem | Authority |
|---|---|---|
| `ux:178` | Cap-stack example prints **`+0 breakthrough`** | Prohibited by name at `docs/design-research/DESIGN.md:994` ("A term the tool cannot observe never renders as zero"). Same rejected value as the live string at `stat-band.blade.php:153` |
| `ux:98, 447, 100` | Theme, display timezone and the failure-estimate toggle persist to **`localStorage`** | Owner ruling 2026-09-27: preferences go to **SQLite**, because `PRD.md` §6.12 cuts browser-side storage as a second source of truth. Now written into D-104 |
| `ux:29` vs `ux:98` | The doc says "Default to light theme" and then defaults Theme to **System** | Not actually a contradiction — the corpus resolves it: light is the *base palette*, resolution is stored → `prefers-color-scheme` → light. The doc states the rule and the fallback without connecting them |
| `ux:17` | "Run List … **Default landing**" | `DESIGN.md:181`: the catalog index is "**The desk's front page** and the most complex grid", owner priority |
| `ux:247-262` | One universal 4-step turn flow | `config/scenarios.php:83,116` compose per-scenario `steps` (Unity Cup `facility`, Trackblazer `shop`); a fixed 4-step flow is the scenario-hardcoding D-240/G-40 forbids |
| `flows:327-342`, `839-841` | Adds League Rank, Team Race countdown, GP deadline and restock timers to the **resource strip** | `DESIGN.md:1020` caps the strip at zero-to-five items (turn, Energy, Fans always, then the scenario's widgets in `config/scenarios.php:82,115`). These facts are sourced; they belong in panels, not on the strip |
| `flows:968-983` | Race Fatigue chip prints bare numbers ("Mood Down 33%") | Values trace to `docs/scenarios/04` via `SCENARIO-DIFFERENCES.md:179-185`, but **D-230 as amended requires client data or two sources before a percentage prints as a number**, and `90%+` is a lower bound. Written before that rule existed |
| `flows:188-190` | "need **3+ of 5** to win" the Team Race | Misreading. `06-unity-cup-gametora.md:103` says "aim for at least **3 circles**" — a displayed win-odds *estimate* used as a safety margin, not a victory condition |
| `flows:576` | Item called "**Anklet**, 50c" | `05-trackblazer-gametora.md:108` names it **Ankle Weights**, and records that **no Wit version exists** — a constraint the spec drops |
| `flows:27, 468` | "inheritance" used as the system noun | `REFERENCE:571` plus the owner ruling: system = **Inspiration**, screen = **Legacy Select** |
| `ux:37` | Cites "DESIGN §6.20" for cards-not-a-table | §6.20 is the Race Calendar. The rule is D-60 / `DESIGN.md:1301`. Right rule, wrong pointer |
| `flows:377`, `359`, `1008` | Spirit gauge "0-100", "purple ring", "orange at full", Climax "1st of **5**" | No authority carries a gauge scale, those two colours, or a finale field size |

### 6.2 Worth adopting (patterns, per D-283)

`UX §3` **input taxonomy** (every input, option, toggle, and an explicit "what is NOT an input") and
`§4` **run-lifecycle state machine** are structures the corpus does not have, and they are the right
neighbours to D-223's six-state burst machine. `flows §4.1/4.2` **component-state tables** are the same
idea at component granularity. Adopting the *shape* costs nothing and makes G-7 (four states per data
view) auditable; adopting their *contents* still requires the citations above.

### 6.3 A cadence fact the corpus was missing

`flows` and `ux` both state Unity Cup Team Races occur **every 6 months**. That is sourced —
`06-unity-cup-gametora.md:32` and `:99` — and the research corpus never recorded it, which is why an
earlier round could call it unsourced. It belongs in §10n's Unity Cup column and, if the strip or a panel
ever counts down to one, in `config/scenarios.php`. Not added here: that is a data change to a shipped
config file, and the phase is fenced against it.

### 6.4 Consequences of the two rulings taken this round

- **D-104 rewritten**: preferences persist in SQLite, not `localStorage`; the original
  "a local single-user tool has no server to store a preference on" premise was simply false —
  the app has SQLite. Resolution order and the inline-head-script requirement are kept.
- **Blocker recorded**: `AGENTS.md` requires every new table to cite a PRD requirement. **US-7** covers
  the timezone. **Nothing in PRD §2 covers a theme or estimate-toggle preference**, so a `preferences`
  table needs a PRD line first. Recorded as an Architect escalation rather than solved by keeping the
  value in the browser.
- **`components/layout.blade.php` migrated to tokens and given the pre-paint theme resolver**, because
  "keep system-follow" had no implementation at all: there was no `data-theme` and no script anywhere,
  so the app could only ever render light. Verified in a browser with the OS emulated both ways.
- **KI-1 closed as a side effect**: the layout requested `resources/js/app.js` while the manifest key is
  `app.ts`, so *every* page was 500 and the theme behaviour could not be observed through the real app.
  `/training-runs` and `/review` now return 200; `/umamusume` still 500s on KI-2, a different defect.
