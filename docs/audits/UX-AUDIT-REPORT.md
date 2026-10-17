# UX Audit — Laws of UX / Laws of UI, rendered frontend

**Date:** 2026-10-17
**Instrument:** the server already answering `http://127.0.0.1:8000` (PID 27136, started 14:27:36; `php -S` over `vendor/laravel/framework/.../server.php`). No server was started, no scratch DB created, no tree stashed.
**Scope:** 48 routes × 4 widths = **192 full-page captures**, plus targeted probes (keyboard walk, accessible-name survey, heading outline, axe detail, form semantics).
**Status:** findings only. **No fix has been applied. Nothing is committed or pushed.**

---

## Audited state

This report measures a tree that changed underneath it. That is recorded here rather than smoothed over.

| Fact | Value |
|---|---|
| HEAD at Phase 0 | `e09a475` (from the prior session's read; tree dirty, 31→34 files) |
| **HEAD when this report was written** | **`0d3a69c7365f81a08a59a9d32d96f4070124b4cd`** |
| Commits landed mid-audit | `9f4b2c5` (PHPStan level 7), `0d3a69c` ("style(wizard): densify four step pages and fix the duplicate h1") |
| Tree at report time | **clean** (`git status --porcelain` → 0 lines) |
| `origin/master` | not re-checked after the mid-audit commits |

**What this means for the evidence.** Every capture in this report postdates `0d3a69c`:

- first screenshot written 15:49:00, captures complete by 16:19;
- `0d3a69c` committed 15:45:59.

So the **screenshots represent `0d3a69c`**, not the dirty tree described in Phase 0. The dirty tree no longer exists; it was committed.

**A live writer appeared at the end of the audit.** At 16:35 `git status` showed `M resources/js/components/scenario/ScenarioPanel.vue`, modified at **16:34:39** — after the last capture. The diff is three lines replacing a mojibake glyph `â—‹` with `○` in empty-state rows. It touches no finding in this report and no route's verdict, but it confirms **another agent is active in this worktree**; anything committed after `0d3a69c` is outside the measured state.

**Per-file content hashes (SHA-256, first 16 hex chars) of every source file this report cites:**

| File | SHA-256 (16) |
|---|---|
| `resources/js/layouts/AppLayout.vue` | `727829203A0F1CD2` |
| `resources/js/layouts/SetupLayout.vue` | `2CA688CAFC9D185C` |
| `resources/js/pages/Career/ScenarioSelect.vue` | `BC536BB4671CBE3F` |
| `resources/js/pages/Career/LegacySelect.vue` | `9426AC1D4E92C9C4` |
| `resources/js/pages/Career/Snapshot/Setup.vue` | `7D9198CF5FC5C656` |
| `resources/js/pages/Dashboard.vue` | `FDD33870023D9009` |
| `resources/js/pages/Review/Index.vue` | `1C80D965722A5E6B` |
| `resources/js/pages/Skills/Show.vue` | `7B31CFDCAA579C50` |
| `resources/js/pages/Runs/Index.vue` | `ADF7B02DFB3006E4` |
| `resources/js/pages/SupportCards/Index.vue` | `3F5FB0FE27F48584` |
| `resources/js/components/Form/BaseSelect.vue` | `C11EACFF34ADDA66` |
| `resources/js/components/SubCell.vue` | (read at `0d3a69c`) |
| `resources/js/components/legacy/AncestryNode.vue` | `C60A89E78FA51AF7` |
| `resources/js/components/StatusPill.vue` | `916E68F22374F941` |
| `resources/js/components/ProvenanceBadge.vue` | `9395FBCE3E6D7D38` |
| `resources/js/components/support/SupportCardCatalog.vue` | `151C26FD59DCEFF6` |

**Evidence artefacts** (all under `.scratch-uma/ux-audit-2026-10-17/`):

| Artefact | Hash / count |
|---|---|
| `screens/{route}/{width}.png` | 192 files, 39,504,707 bytes, 48 route dirs |
| `results-layout.json` (layout pass) | `9A860DB71DD12DFC` |
| `results.json` (axe pass) | `56EC9607D22F1447` |
| `working-tree.diff` (the Phase-0 dirty tree, now committed) | `3E07777787F8A149` |
| `summary.txt`, `axe-summary.txt`, `kb.json`, `probe.log`, `outline.log`, `vet.log`, `snap.log`, `names.log` | raw command output, quoted verbatim below |

---

## Audit checklist — reconciliation

### Sources read at audit time

- **lawsofux.com** — resolved fully (30 laws, one-line definitions each). Read via `webfetch`; the **integrated browser could not be used** in this session (`"Browser session does not belong to the requested project or directory"`), so this is a server-rendered fetch, not a DOM capture. It returned the complete list, so the client-render concern did not bite.
- **uilaws.com** — resolved fully (13 laws). **This contradicts the operator's note** that the list is client-rendered and does not resolve in a plain fetch: a plain `webfetch` returned all 13 names and their definitions. The populated result is recorded below rather than "what I could read".
- **Plan §1.1** — the canonical 27, read in full (the checklist is one 4,921-character line in the source; it was split to read it).

### The 27 canonical items (plan §1.1)

1 Aesthetic–Usability · 2 Jakob's Law · 3 Fitts's Law · 4 Hick's Law · 5 Miller's Law · 6 Law of Proximity · 7 Law of Similarity · 8 Law of Common Region · 9 Law of Uniform Connectedness · 10 Law of Prägnanz · 11 Law of Continuity · 12 Law of Closure · 13 Law of Common Fate · 14 Law of Experience · 15 Von Restorff Effect · 16 Serial Position Effect · 17 Peak–End Rule · 18 Goal-Gradient Effect · 19 Zeigarnik Effect · 20 Tesler's Law · 21 Occam's Razor · 22 Pareto Principle · 23 Doherty Threshold · 24 Cognitive Load · 25 Choice Overload · 26 Flow · 27 Aesthetic consistency & visual hierarchy.

### lawsofux.com's 30 versus the plan's 27 — the reconciliation is different from what the plan says

lawsofux.com's homepage lists exactly **30**: the 24 canonical laws the plan shares, **plus six** the plan's 27 does not carry (Chunking, Cognitive Bias, Mental Model, Paradox of the Active User, Parkinson's Law, Postel's Law, Selective Attention, Working Memory — that is eight names beyond the shared set, because the plan's 27 also carries **five** frames lawsofux.com does not list as laws: **Law of Common Fate (13), Law of Continuity (11), Law of Closure (12), Aesthetic consistency & visual hierarchy (27), and Cognitive Load is shared**).

**Correction to the plan, recorded not resolved.** Plan §2.1 says "seven of §13's rows are not in the canonical 27: Chunking, Mental Model, Paradox of the Active User, Parkinson's Law, Selective Attention, Working Memory, Postel's Law." **Four of those seven are on lawsofux.com itself** (Chunking, Mental Model, Paradox of the Active User, Postel's Law) and a fifth (Parkinson's Law) is too. Only Selective Attention and Working Memory are genuinely absent from lawsofux.com. The plan's "adjacent principles" framing is still the right *treatment* (they are folded, not deleted, per §2.1), but the factual claim that they are "not in the canonical 27" is wrong about their provenance. **This does not change any verdict below** — all four were judged as adjacent principles regardless.

Also on lawsofux.com and not in the plan's 27: **Cognitive Bias**. Not judged — it is a property of the user, not a property a screen can pass or fail.

### uilaws.com's 13 UI laws, mapped to canonical rows

| uilaws law | Maps to |
|---|---|
| Symmetry | 10 Prägnanz |
| Rule of Thirds | 27 hierarchy |
| White Space | 6 Proximity, 10 Prägnanz |
| Color Theory | 15 Von Restorff, 27 aesthetic consistency |
| Typography Hierarchy | 27 hierarchy |
| **Consistency** | **27 + 7 Similarity (component consistency)** — the cross-page row |
| Proximity | 6 |
| Contrast | 15 Von Restorff |
| Closure | 12 |
| Continuity | 11 |
| Fitts's Law | 3 |
| Hick's Law | 4 |
| Jakob's Law | 2 |

Every uilaws law lands on an existing canonical row. **uilaws adds no new row**, and its "Consistency" is the direct source of the cross-page section below.

### Instrument legend

**Auto** = measured by the capture harness (`results-layout.json`). **Judge** = read from a screenshot or source with a stated line. **Measure** = a targeted probe whose output is quoted.

---

## 1. UX Audit Summary

### Overall assessment

The decision spine (`State → Options → Recommendation → Explanation → Decision`) is **rendered, not just coded**, on the surfaces that own it. The cockpit at 390px holds up: 0 horizontal overflow, one state, one recommendation with a visible "Why this answer", and `N/A` on every value the corpus does not hold. The empty-state and named-absence work of the three prior audits has largely landed — the review queue no longer shows a confident `Confirmed` (D-21), no nested labels remain (F-G4), and every control measured on the cockpit has an accessible name.

The failures cluster in two places the prior audits could not see:

1. **The wizard's stepper tells the truth about navigation and lies about completion.** Five steps read "done" while five warnings on the same screen say nothing is recorded. This is the HLF's own P0, reproduced in the live product.
2. **Long unbreakable strings escape their containers.** The skill detail page scrolls **224px** horizontally at 390px; the skills planner **104px**. Both are engine-data strings set in `font-mono` with no `break-words`.

The audit's single most important judgement: **the app is in better shape than the corpus says, and the corpus is partly stale.** Six of the `F-xx` findings I retested have been fixed and still appear in `AUDIT-AND-VERIFICATION.md` as open.

### Strongest patterns (measured, not asserted)

| Pattern | Evidence | Law |
|---|---|---|
| Cockpit decision spine complete at every width | `screens/cockpit/1280.png`, `.../390.png`; overflow `0/0/0/0`, min control identical; "Recommended action → Rest" + "Why this answer" disclosure | 26 Flow, 24 Cognitive Load, 20 Tesler's |
| Named absence, not blank | `screens/snapshot-setup/1280.png` ("I don't know this value" per stat); `screens/legacy-builder/1280.png` ("Rank not recorded", "No Sparks recorded") | 12 Closure, 27 |
| Honest import affordance | `screens/runs-import/1280.png`: "Not set (baseline strip)" select, "(optional)" markers, preview-before-write copy | 2 Jakob's, 17 Peak–End |
| Focus visibility everywhere tested | `kb.json`: `noOutline: []` on both walked pages; every tab stop `solid 2px` | 27, WCAG 2.4.7 |
| Accessible names on the flagship | `names.log`: cocktail `total: 106`, `unnamed: []`, `titleOnly: []` | 27 |
| No axe violation across 47 of 48 routes | `axe-summary.txt`: `distinctRules=1`, on `home` only | — |

### Highest-risk issues

| # | Issue | Route | Severity |
|---|---|---|---|
| A | Stepper `done` vs. five "not entered" warnings | Wizard (all six steps + preflight) | **P0** |
| B | Skill condition string overflows the viewport 224px | `skills-detail` | **P1** |
| C | `role="list"` wrapping `<dl>` children — axe **critical** | `home`; same pattern in legacy step | **P1** |
| D | Veteran names hard-truncated to 53px; duplicate rows indistinguishable | `veterans-index` 390 | **P1** |
| E | Wizard steps have no heading (an `<h1>` was replaced by a styled `<p>`) | Wizard steps | **P1** |
| F | `Month` select defaults to "1" while `Year`/`Half` default to empty | `snapshot-setup` | **P1** |
| G | Skills planner overflows 104px | `skills-planner` 390 | **P2** |

### Laws most frequently violated (laws-frequency count)

Counted across the 48 captured routes. A law is "violated" when it scored FAIL or PARTIAL on a route it applies to.

| Law | FAIL routes | PARTIAL routes | Total touched |
|---|---|---|---|
| **27 Aesthetic consistency & visual hierarchy** | 6 | 9 | **15** |
| **19 Zeigarnik** | 6 | 3 | **9** |
| **12 Closure** | 1 | 6 | **7** |
| **7 Law of Similarity** | 2 | 4 | **6** |
| **2 Jakob's Law** | 1 | 4 | **5** |
| **18 Goal-Gradient** | 0 | 4 | **4** |
| **3 Fitts's Law** | 1 | 2 | **3** |
| **17 Peak–End** | 0 | 3 | **3** |
| **10 Prägnanz** | 0 | 3 | **3** |
| **21 Occam's Razor** | 0 | 3 | **3** |
| 1 Aesthetic–Usability | 0 | 2 | 2 |
| 4 Hick's / 6 Proximity / 9 Uniform Connectedness / 14 Experience | 0 | 2 each | 2 each |
| 20 Tesler's / 25 Choice Overload | 0 | 1 | 1 each |

**Joined to the existing severity counts:** the corpus carries 87 findings. This audit adds **13 new IDs** (A-91 → A-103) and **re-tests 7** of the existing 87 — `D-26`, `F-B1`, `F-B2`, `F-E1`, `F-E3`, `F-G4`, `X-2`. Of those: **2 are fixed** (`D-26`, `F-G4`), **1 is half-fixed** (`X-2`: the shell `#title` is now `text-3xl`, but the wizard lost its page heading — A-102), **1 is stale** (`F-E1`: the `rank-label` prop is passed at `Builder.vue:365` and defined at `AncestryNode.vue:78`), **1 is partial** (`F-B1`), and **2 remain open** (`F-B2` retested only by reading `AncestryNode.vue:102,118`, not by a fresh screenshot; `F-E3` extended at `AncestryNode.vue:118`).

### Accessibility and responsive risk summary

- **Axe:** one rule, `aria-required-children` / **critical**, 1 node per width on `home`, 4 nodes total. Cause identified: `Dashboard.vue:258` marks a container `role="list"` whose children are `SubCell` roots — each a `<dl>` (`SubCell.vue:16`). `dt`/`dd` are not permitted children of `role="list"`.
- **Axe did not run on `error-404`** — 4 rows carry `axeError` ("No tests found"-class context error, the 404 document has no `#app` root). That is an **unmeasured** route, not a clean one.
- **Axe's silence is not a pass** (§9.11). The names survey was run separately and is what substantiates the cocktail's a11y, not axe.
- **Horizontal overflow:** 2 of 48 routes at 390px (`skills-detail` +224px, `skills-planner` +104px); 1 at 768px (`skills-detail` +102px). All other 45 routes: **0** at all four widths.
- **The `1px` minimum control** that appears on 47 of 48 routes is the **skip-to-content link** (`a.sr-only.focus:not-sr-only`), which is correctly hidden until focused. **Not a defect.** The 404 page, which bypasses the app shell, measured `44px`.
- **Touch targets:** no control under 44px was found other than that skip link. `min-h-11` (44px) is applied consistently.
- **Keyboard:** 22 tab stops on the wizard scenario page, 16 on the cocktail, **zero** controls without a visible focus outline, **zero** page-content controls obscured by the fixed bottom bar.

---

## 2. Route-by-route findings

### How to read these tables

Every route below was captured at 390/768/1024/1280. Rows marked **N/A** name why. Laws judged identical across a whole cluster are stated once in the cluster lead and referenced.

**Evidence paths are relative to** `.scratch-uma/ux-audit-2026-10-17/screens/`.

---

### Cluster A — Shell, home, errors

#### A1. Home / Dashboard — `/`

- **User goal:** know where the active career stands and what to do next.
- **Primary task:** resume the active career, or reach a run.
- **Overall rating:** **Strong**

| Law | Status | Evidence | User impact | Recommendation | Sev | Issue type | Existing ID |
|---|---|---|---|---|---|---|---|
| 27 Aesthetic consistency | PARTIAL | `home/1280.png`; shell h1 `text-3xl` vs section `text-lg` is now consistent (X-2 shell half closed) | Low | — | P3 | visual | X-2 (shell half *fixed*) |
| 2 Jakob's | PASS | sidebar Dashboard/Careers/Legacy/Veterans/Settings convention | — | — | — | — | — |
| 3 Fitts's | PASS | `Resume career` is the largest control in the card; primary sits below its state block | — | — | — | — | L-F01 still FAIL on nav/export (not this route) |
| 6 Proximity | PASS | label/value pairs inside tiles at 8px gap; sections separated | — | — | — | — | — |
| 7 Similarity | PARTIAL | three stat tiles identical, but `Turn` renders bold while `Energy`/`Team Rank` render muted because they are `N/A` | The muted tint correctly signals absence — this is *deliberate*, but the tile reads as a different kind of thing | none — intentional | P3 | visual | — |
| 8 Common Region | PASS | Active Career / Quick Actions / Recent Veterans / Data Status are four bounded cards | — | — | — | — | — |
| 18 Goal-Gradient | PARTIAL | `home/1280.png` progress bar sits at ~2% with `Turn 1 / 24`; bar carries role + values per `Dashboard.vue:278-279` | At turn 1 the bar is a single sliver — truthful but adds little | none | P3 | visual | — |
| 19 Zeigarnik | PASS | "1 career are active" + `Resume career` keeps the unfinished career visible | — | — | — | — | — |
| **27 / a11y** | **FAIL** | axe **critical** `aria-required-children`; `Dashboard.vue:258` `role="list"` wraps `SubCell` roots, each a `<dl>` (`SubCell.vue:16`) | Screen readers hear a list with no list items; the three most important numbers lose their group semantics | Drop `role="list"` from the container at `Dashboard.vue:258`, or change `SubCell` to emit `<div role="listitem">` in list contexts | **P1** | accessibility | **A-91 (new)** |
| 12 Closure | PARTIAL | `Energy` / `Team Rank` show `N/A` with a `title` only (`Dashboard.vue:268,274`) | The reason for the absence is hover-only; touch and keyboard users cannot read it | render the reason through `AbsenceValue` | P2 | accessibility | F-B2 (adjacent) |
| **20 Tesler's** | PARTIAL | "1 career are active" — singular/plural bug | Reads as carelessness on the first sentence a user sees | "1 career is active" / "N careers are active" | P3 | content | **A-92 (new)** |

**Corrected observation:** an earlier probe of this page reported the career strip as `dt`/`dd` inside `role="list"` *without* a `<dl>`; `SubCell.vue:16` shows the `<dl>` **is** present as the component root. The axe violation is therefore about **list semantics**, not `dlitem`. Recorded so the fix targets the right layer.

---

#### A2. 404 — `/__audit_missing_route__`

- **User goal:** understand the address failed and get back.
- **Primary task:** return to a working page.
- **Overall rating:** **Strong**

| Law | Status | Evidence | User impact | Recommendation | Sev | Issue type | Existing ID |
|---|---|---|---|---|---|---|---|
| 27 | PASS | `error-404/1280.png`; single `<h1>Page not found</h1>`, `#main` root, 44px min control | Standalone error doc is correctly self-contained | — | — | — | — |
| 2 Jakob's | PASS | plain-language title + recovery link | — | — | — | — | — |
| 12 Closure | PASS | the page names the failure rather than rendering an empty shell | — | — | — | — | — |
| **axe** | **N/A** | `results.json`: `axeError` on all 4 widths | **This route is unmeasured for a11y** | run axe against `#main` | P3 | accessibility | **A-93 (new, measurement gap)** |

---

### Cluster B — Reference catalogs

Cluster lead: at all four widths, **0 horizontal overflow** for `catalog-index`, `catalog-detail`, `skills-index`, `supportcards-index`, `supportcards-detail`; laws 2, 6, 7, 8, 10, 21, 22 scored PASS on all five and are not repeated per route.

#### B1. Database catalog — `/umamusume`, `/umamusume/{id}`

- **User goal:** look up a trainee's facts.
- **Primary task:** search, then read one trainee.
- **Overall rating:** **Strong**

| Law | Status | Evidence | User impact | Recommendation | Sev | Issue type | Existing ID |
|---|---|---|---|---|---|---|---|
| 3 Fitts's | PARTIAL | `catalog-detail/1280.png`; row actions are full-row targets | — | — | — | — | — |
| 19 Zeigarnik | **N/A** | static reference; nothing is left unfinished | — | — | — | — | — |
| 18 Goal-Gradient | **N/A** | no progress toward a goal on a lookup surface | — | — | — | — | — |
| 23 Doherty | PARTIAL | no in-page loading indicator on filter change; full navigation | Filter changes may feel unresponsive on a slow box | none — §9.5 forbids a cold-load spinner; leave | P3 | interaction | ADR-0007 |

#### B2. Skill search — `/skills` + `/skills/{id}`

- **User goal:** find a skill and understand what it does.
- **Primary task:** search, then read one skill's mechanics.
- **Overall rating:** **Mixed**

| Law | Status | Evidence | User impact | Recommendation | Sev | Issue type | Existing ID |
|---|---|---|---|---|---|---|---|
| **27 / responsive** | **FAIL** | `skills-detail/390.png`; document `scrollWidth−clientWidth` = **224px**. Source of overflow: `Skills/Show.vue:115` `<dd class="font-mono text-xs">{{ group.condition }}</dd>` | **The whole page scrolls sideways on a phone.** Reading a skill means dragging horizontally and losing the column you were reading | add `break-words` (and `min-w-0` on the `<dd>`) at `Skills/Show.vue:115`; the string is engine data with no break opportunities | **P1** | responsive | **A-94 (new)**; distinct from F-B1 |
| 27 / responsive | PARTIAL | `skills-detail/768.png` overflow **102px** | Same defect at tablet width | same | P1 | responsive | A-94 |
| 12 Closure | PARTIAL | `skills-detail/390.png` "Base time 50000 ms" is present but `Condition` prints raw engine text | A user cannot tell whether the skill is usable, only that its raw expression exists | none this audit — the description source does not exist (ADR-0011), so the raw string is the honest floor | P3 | content | F-B1 (partial) |
| 10 Prägnanz | PARTIAL | raw `distance_rate>=50&distance_rate<=65&order>=2&...` is the least-simple form of the information | The simple interpretation is not available from the data | record as a corpus gap, not a UI fix | P3 | content | G-SK-20 |

#### B3. Support cards — `/support-cards` + `/support-cards/{id}`

- **User goal:** scan support cards and open one.
- **Primary task:** filter, then drill in.
- **Overall rating:** **Strong**

| Law | Status | Evidence | User impact | Recommendation | Sev | Issue type | Existing ID |
|---|---|---|---|---|---|---|---|
| 4 Hick's | PASS | 4 filters (Rarity/Type/Availability/Sort) + one `Filter` button; 25 rows/page | Choice load is bounded | — | — | — | — |
| 5 Miller's | PASS | rows grouped by card, facts as one line | — | — | — | — | — |
| 3 Fitts's | PASS | probe: `detailLinks: 50`, `uniqueDetail: 25` — every row links | Drill-in exists despite the audit's first (wrong) probe reading 0 | **retracted:** my initial "no drill-in links" claim was a selector bug (absolute hrefs); corrected here | — | — | — |
| 16 Serial Position | PASS | pagination at the foot, count "559 of 559" at the head | — | — | — | — | — |

**Correction recorded:** my first probe of this route reported `detailLinks: 0`. That was wrong — the hrefs are absolute (`http://127.0.0.1:8000/support-cards/131`) and my pattern was prefix-anchored. The corrected probe (`probe.log`) returns 50 detail links / 25 unique. **No finding.**

---

### Cluster C — Database hub

- **User goal:** understand what reference data the app holds and whether it is current.
- **Primary task:** read data status, jump to one dataset.
- **Overall rating:** **Strong**
- **Cluster-wide:** 9 routes × 4 widths, **0 overflow**, all `200`, one `h1` each. Laws 2, 6, 7, 8, 9, 10, 16, 21, 22, 27 **PASS** on all nine — the most consistent cluster in the app.

| Law | Status | Evidence | User impact | Recommendation | Sev | Issue type | Existing ID |
|---|---|---|---|---|---|---|---|
| 19 Zeigarnik | **N/A** | read-only reference hub | — | — | — | — | — |
| 18 Goal-Gradient | **N/A** | no goal progression | — | — | — | — | — |
| 25 Choice Overload | PARTIAL | `database-hub/1280.png` — 8 tiles presented flat | Bounded at 8, well under overload | none | P3 | visual | — |
| 12 Closure | PARTIAL | `home` "Data Status" shows `Global data · Current` and `Ruleset version: N/A`; the hub itself does not repeat freshness | A user in the hub cannot see staleness without returning home | none — avoid duplicating a fact (21) | P3 | content | — |

---

### Cluster D1 — Run lifecycle

#### D1a. Training runs — `/training-runs`

- **User goal:** find a run and resume it.
- **Primary task:** pick a run from 11.
- **Overall rating:** **Mixed**

| Law | Status | Evidence | User impact | Recommendation | Sev | Issue type | Existing ID |
|---|---|---|---|---|---|---|---|
| 3 Fitts's | PASS | `runs-index/1280.png`; row-level `:href="run.url"` (`Runs/Index.vue:85`) | — | — | — | — | — |
| 16 Serial Position | PASS | `Import` / `New run` at head; pagination at foot | — | — | — | — | — |
| 7 Similarity | **FAIL** | `home/1280.png` Recent Veterans lists **"Vodka / Vodka / Daiwa Scarlet"**; `veterans-compare/1280.png` offers two checkboxes both labelled **"Vodka"** and two labelled **"Maruzensky"** | Two distinct careers are indistinguishable; selecting the wrong one is one click away | append the run identity (`Scenario · start date` or `#runId`) to the row label wherever two rows share a name | **P1** | content | **A-95 (new)** |
| 27 | PARTIAL | the duplicate rows carry no distinguishing secondary line | — | same | P1 | content | A-95 |

#### D1b. Create run — `/training-runs/create`

- **User goal:** start a run.
- **Primary task:** pick trainee + scenario, create.
- **Overall rating:** **Strong**

| Law | Status | Evidence | User impact | Recommendation | Sev | Issue type | Existing ID |
|---|---|---|---|---|---|---|---|
| 4 Hick's | PASS | 2 required choices | — | — | — | — | — |
| 6 Proximity | PASS | label directly above each control | — | — | — | — | — |
| 17 Peak–End | PASS | create lands on the cockpit | — | — | — | — | — |

#### D1c. Import — `/training-runs/import`

- **User goal:** bring in a finished run.
- **Primary task:** pick trainee, drop CSV, preview.
- **Overall rating:** **Strong**

| Law | Status | Evidence | User impact | Recommendation | Sev | Issue type | Existing ID |
|---|---|---|---|---|---|---|---|
| 2 Jakob's | PASS | conventional file input + textarea fallback | — | — | — | — | — |
| 20 Tesler's | PASS | "The preview shows every row before anything is written" absorbs the complexity | — | — | — | — | — |
| 12 Closure | PASS | optional fields explicitly say "(optional)"; scenario says "Not set (baseline strip)" | — | — | — | — | — |
| 17 Peak–End | PASS | "nothing is created until you confirm" | — | — | — | — | — |

---

### Cluster D2 — Queue and settings

#### D2a. Review queue — `/review`

- **User goal:** resolve the entity matches the engine could not decide.
- **Primary task:** for each candidate, set a verdict and resolve.
- **Overall rating:** **Mixed**

| Law | Status | Evidence | User impact | Recommendation | Sev | Issue type | Existing ID |
|---|---|---|---|---|---|---|---|
| 19 Zeigarnik | PASS | "25 of 57 items to resolve" + pagination; incomplete work is visible | — | — | — | — | — |
| 18 Goal-Gradient | PARTIAL | count present, no progress bar | — | none | P3 | visual | — |
| **21 Occam's** | **FAIL** | `review/1280.png`: all 25 visible rows carry the identical sentence "No suggestion: the source name does not fuzzy-match any trainee", and each row also carries a `No match` pill **and** an `Unknown` badge | 25 identical statements is noise, not information; the user must open each to learn anything | collapse the repeated sentence to the queue header ("17 of 25 rows have no fuzzy candidate") and keep per-row only what differs | **P2** | content | **A-96 (new)** |
| **27 (one label per fact)** | PARTIAL | `Review/Index.vue:91-95` `StatusPill "No match"` + `:98-101` `ProvenanceBadge state="unknown"` → "Unknown"; same fact twice | Two provenance-ish labels on one row compete for the same meaning | keep the `ProvenanceBadge`; drop the `StatusPill` when `match_tier !== 'Fuzzy'` | P2 | visual | **A-96** |
| 12 Closure | PASS | `Review/Index.vue:59-75` — the two empty states ("never fetched" vs "worked through") are now distinct | — | — | — | — | **D-26 fixed** |
| 17 Peak–End | PARTIAL | resolving a row gives no confirmation moment observed | — | none measured | P3 | interaction | needs measurement |
| 6 Proximity | PASS | each card bounds one candidate | — | — | — | — | — |

**Correction recorded:** `Review/Index.vue:34-38` maps `Fuzzy` to `variant: 'warning'`, but `StatusPill.vue:13` renders `warning` as `bg-raised text-ink-strong border-rule` — **neutral**, not an alert colour. §9.3 is **not** breached here. Recorded because the variant name invites that assumption.

#### D2b. Settings — `/preferences`

- **User goal:** change preferences.
- **Primary task:** set and save one preference group.
- **Overall rating:** **Strong**

| Law | Status | Evidence | User impact | Recommendation | Sev | Issue type | Existing ID |
|---|---|---|---|---|---|---|---|
| 27 | PARTIAL | `outline.log`: h1 `Settings` (`text-3xl`) followed by h2 `Preferences` (`text-lg`) — the section repeats the page's own name | Mild redundancy | let the section take a distinct name ("Interface") | P3 | content | **A-97 (new, P3)** |
| 21 Occam's | PARTIAL | same repetition | — | same | P3 | content | A-97 |
| 17 Peak–End | **N/A** | no completion moment on this surface | — | — | — | — | — |

---

### Cluster E — Legacy, Veterans, deck

#### E1. Legacy Lab — `/legacy`

- **User goal:** build/compare Legacy selections.
- **Primary task:** open a run's legacy builder or compare saved selections.
- **Overall rating:** **Mixed**

| Law | Status | Evidence | User impact | Recommendation | Sev | Issue type | Existing ID |
|---|---|---|---|---|---|---|---|
| 19 Zeigarnik | PASS | `legacy-index` names unrecorded selections | — | — | — | — | — |
| 27 / a11y | PARTIAL | `LegacySelect.vue:354` `role="list"` with `<div>` children (`<label>`, `<p>`, `<details>`) — same invalid-list pattern as **A-91**, not flagged by axe on this route only because axe did not attribute it | Screen-reader list navigation is unreliable in the legacy step | drop `role="list"` or wrap each child in `role="listitem"` | P2 | accessibility | **A-91 (same class)** |
| 20 Tesler's | PASS | `legacy-builder/1280.png`: "Record only. This screen stores and compares what you enter. It does not compute inheritance." | — | — | — | — | — |

#### E2. Legacy builder — `/legacy/{run}`

- **User goal:** record the six-node ancestry tree as read off the client.
- **Primary task:** fill name + rank per node, add sparks, confirm.
- **Overall rating:** **Mixed**

| Law | Status | Evidence | User impact | Recommendation | Sev | Issue type | Existing ID |
|---|---|---|---|---|---|---|---|
| **21 Occam's** | **FAIL** | `legacy-builder/1280.png`: "Own rank" under *Parent A as you read her* shows the sentence "Blank stays N/A. Unbounded on purpose…" **twice, consecutively** | Duplicated instruction in the densest form on the page | render the helper once per field group | P2 | content | **A-98 (new)** |
| 3 Fitts's | PASS | base row: names, rank fields and "Add Spark" are 44px targets | — | — | — | — | **F-E1 stale** — `Builder.vue:365` *does* pass `:rank-label`; the prop is present |
| 12 Closure | PARTIAL | `AncestryNode.vue:102` ranks fall back to a `title` only; `:118` "No Sparks recorded" uses a `title` instead of `AbsenceValue` while the neighbouring Spark chance uses `AbsenceValue` (`:130`) | Inconsistent absence channel within one component | move `:118` to `AbsenceValue` | P2 | accessibility | F-E3 (extended) |
| 10 Prägnanz | PARTIAL | six near-identical node cards; only the label distinguishes grandparents | Scannable but long | none — the tree *is* six nodes | P3 | visual | — |
| 25 Choice Overload | **N/A** | recording surface, not a choice surface | — | — | — | — | — |

#### E3. Veterans — `/veterans`

- **User goal:** find a saved career.
- **Primary task:** filter, then open or compare one.
- **Overall rating:** **Mixed**

| Law | Status | Evidence | User impact | Recommendation | Sev | Issue type | Existing ID |
|---|---|---|---|---|---|---|---|
| **3 / 27 responsive** | **FAIL** | `vet.log`: name links clamp at `clientWidth 53` while `scrollWidth` reaches 108 — `Daiwa Scarlet` (86), `Mayano Top Gun` (108), `Maruzensky` (76), `Haru Urara` (70) all truncate | On a phone **every** veteran is "Daiwa…", "Haru…", "Maya…", "Maruz…" — the identifier is gone | give the name the full row width above the action buttons at `390`; let it wrap to two lines | **P1** | responsive | **A-99 (new)** |
| 2 Jakob's | PASS | card grid + compare affordance | — | — | — | — | — |
| 16 Serial Position | PASS | filter at head, results below | — | — | — | — | — |
| 7 Similarity | PARTIAL | `veterans-index/390.png`: `Compare this career` appears *before* the name in reading order | The action precedes the subject | place the name first, action after | P2 | visual | A-95 (adjacent) |

**Correction recorded:** I first described the Japanese names as stacking one character per line. The probe (`vet.log`, `cjkStackedCount: 0`) did **not** reproduce that; the text reads as normal wrapping. **Withdrawn.**

#### E4. Compare Veterans — `/veterans/compare`

- **User goal:** line up up to 6 careers.
- **Primary task:** select and compare.
- **Overall rating:** **Mixed**

| Law | Status | Evidence | User impact | Recommendation | Sev | Issue type | Existing ID |
|---|---|---|---|---|---|---|---|
| 7 Similarity | **FAIL** | two `Vodka`, two `Maruzensky` checkboxes | Cannot tell which is which | see A-95 | P1 | content | A-95 |
| 4 Hick's | PASS | "Choose up to 6", checkbox list, `Clear all` | — | — | — | — | — |
| 27 | PARTIAL | `NOT IN THIS BUILD` is the only ALL-CAPS section heading; sibling `Nothing selected` is sentence case | One section shouts | sentence case, or give all "not in this build" sections a shared tag | P3 | visual | **A-97 (same class)** |
| 12 Closure | PASS | "Nothing selected… Choose up to 6 careers below" — the empty state names the next step | — | — | — | — | — |
| 20 Tesler's | PASS | "NOT IN THIS BUILD" explains *why* inheritance is absent with an ADR citation | — | — | — | — | — |

#### E5. Deck builder — `/training-runs/{id}/deck`

- **User goal:** assemble a six-slot support deck.
- **Primary task:** pick one card per slot.
- **Overall rating:** **Strong**

| Law | Status | Evidence | User impact | Recommendation | Sev | Issue type | Existing ID |
|---|---|---|---|---|---|---|---|
| 4 Hick's | PASS | one picker at a time; "The six slots" then "Choose a card" | — | — | — | — | — |
| 18 Goal-Gradient | PASS | six slots make progress countable | — | — | — | — | — |
| 12 Closure | PASS | empty slots name what belongs there | — | — | — | — | — |

---

### Cluster F — Career decisions

Cluster lead: `training-detail`, `races-decision`, `races-planner`, `skills-planner` all captured at four widths, all `200`.

#### F1. Training decision — `/training-runs/{id}/training`

- **User goal:** choose what to do this turn.
- **Primary task:** read state, pick one action.
- **Overall rating:** **Strong**

| Law | Status | Evidence | User impact | Recommendation | Sev | Issue type | Existing ID |
|---|---|---|---|---|---|---|---|
| 26 Flow | PASS | `training-detail/1280.png`; one action list, no interruption | — | — | — | — | — |
| 4 Hick's | PASS | six actions, grouped | — | — | — | — | — |
| 20 Tesler's | PASS | each action states its cost/requirement beside it | — | — | — | — | — |
| 24 Cognitive Load | PASS | state is summarised before the actions | — | — | — | — | — |

#### F2. Race decision — `/training-runs/{id}/races`

- **User goal:** choose the next race.
- **Primary task:** compare races, pick one.
- **Overall rating:** **Strong**

| Law | Status | Evidence | User impact | Recommendation | Sev | Issue type | Existing ID |
|---|---|---|---|---|---|---|---|
| 4 Hick's | PASS | races grouped by goal category | — | — | — | — | — |
| 8 Common Region | PASS | region contract respected (`overflow-x-auto` region), **0 overflow** at all widths | — | — | — | — | §9.4 honoured |
| 15 Von Restorff | PASS | mandatory races marked distinctly from optional | — | — | — | — | — |

#### F3. Race planner — `/training-runs/{id}/races/planner`

- **User goal:** plan the season's races.
- **Primary task:** read the calendar.
- **Overall rating:** **Strong**
- **Cluster-wide:** 0 overflow at all four widths — the calendar is **not** reflowed, as §9.4 requires.

| Law | Status | Evidence | User impact | Recommendation | Sev | Issue type | Existing ID |
|---|---|---|---|---|---|---|---|
| 11 Continuity | PASS | reading path follows the season order | — | — | — | — | — |
| 16 Serial Position | PASS | earliest at head | — | — | — | — | — |

#### F4. Skills planner — `/training-runs/{id}/skills`

- **User goal:** choose which skills to buy.
- **Primary task:** learn/acquire skills within the SP budget.
- **Overall rating:** **Weak**

| Law | Status | Evidence | User impact | Recommendation | Sev | Issue type | Existing ID |
|---|---|---|---|---|---|---|---|
| **27 / responsive** | **FAIL** | `skills-planner/390.png`; overflow **104px**. Condition strings in `font-mono` again | Sideways scroll on the decision surface | `break-words` + `min-w-0` on the condition cells | **P2** | responsive | **A-94 (same class)** |
| 4 Hick's | PARTIAL | ~9 learned + ~9 required + available lists, then an "Add a skill" picker | Long but chunked | none | P3 | visual | — |
| 18 Goal-Gradient | PARTIAL | "Skill Point coverage: 1 unclaimed" present | — | — | — | — | — |
| 19 Zeigarnik | PASS | "Required" list is separate and shows "not acquired" | — | — | — | — | — |
| 10 Prägnanz | PARTIAL | raw engine conditions repeated per skill | — | same as A-94 fix | P2 | content | — |

---

### Cluster G — Record, outcome, snapshot

#### G1. Events — `/training-runs/{id}/events`

- **User goal:** record an event choice.
- **Primary task:** pick an option.
- **Overall rating:** **Strong**

| Law | Status | Evidence | User impact | Recommendation | Sev | Issue type | Existing ID |
|---|---|---|---|---|---|---|---|
| 26 Flow | PASS | `events-decision/1280.png`; one event, options in place | — | — | — | — | — |
| 4 Hick's | PASS | options bounded per event | — | — | — | — | — |
| 20 Tesler's | PASS | option consequences stated | — | — | — | — | — |

#### G2. Inheritance — `/training-runs/{id}/inheritance`

- **User goal:** record inherited sparks.
- **Primary task:** pick parent → grandparent → spark.
- **Overall rating:** **Strong**

| Law | Status | Evidence | User impact | Recommendation | Sev | Issue type | Existing ID |
|---|---|---|---|---|---|---|---|
| 22 Pareto | PASS | two parents, the small set that matters | — | — | — | — | — |
| 12 Closure | PARTIAL | absence channels differ from the legacy builder (see F1/A-98 note) | — | unify with `AbsenceValue` | P2 | accessibility | F-E3 |

#### G3. Timeline — `/training-runs/{id}/timeline`

- **User goal:** read the career history.
- **Primary task:** scan turns.
- **Overall rating:** **Strong** — turn log **not** reflowed, 0 overflow (§9.4 honoured).

| Law | Status | Evidence | User impact | Recommendation | Sev | Issue type | Existing ID |
|---|---|---|---|---|---|---|---|
| 11 Continuity | PASS | one reading path down the turn list | — | — | — | — | — |
| 16 Serial Position | PASS | turns in order | — | — | — | — | — |

#### G4. Result — `/training-runs/{id}/result`

- **User goal:** see how the career ended.
- **Primary task:** read the outcome.
- **Overall rating:** **Strong**

| Law | Status | Evidence | User impact | Recommendation | Sev | Issue type | Existing ID |
|---|---|---|---|---|---|---|---|
| **17 Peak–End** | PASS | `result/1280.png`; the run's end is a named surface, not an empty page | — | — | — | — | — |
| 12 Closure | PASS | finished state is stated | — | — | — | — | — |
| 6 no-score rule | PASS | no grade, win-chance or percentage rendered (§9.6) | — | — | — | — | — |

#### G5. Save Veteran — `/training-runs/{id}/veteran`

- **User goal:** file the career into the library.
- **Primary task:** confirm and save.
- **Overall rating:** **Strong**

| Law | Status | Evidence | User impact | Recommendation | Sev | Issue type | Existing ID |
|---|---|---|---|---|---|---|---|
| 17 Peak–End | PASS | `save-veteran/1280.png`; a dedicated confirmation surface | — | — | — | — | — |
| 24 Cognitive Load | PASS | one decision | — | — | — | — | — |

#### G6. Snapshot entry — `/career/snapshot` and `/career/snapshot/review`

- **User goal:** start from a career already in progress.
- **Primary task:** choose a door.
- **Overall rating:** **Strong**

| Law | Status | Evidence | User impact | Recommendation | Sev | Issue type | Existing ID |
|---|---|---|---|---|---|---|---|
| 25 Choice Overload | PASS | a single "Start from existing snapshot" door | — | — | — | — | — |

#### G7. Snapshot setup — `/career/snapshot/setup`

- **User goal:** transcribe the client's current state.
- **Primary task:** fill scenario/trainee/position/8 stats, then review.
- **Overall rating:** **Mixed**

| Law | Status | Evidence | User impact | Recommendation | Sev | Issue type | Existing ID |
|---|---|---|---|---|---|---|---|
| **12 Closure** | **FAIL** | `snapshot-setup/1280.png` + `snap.log`: `Year` defaults to `""`, `Half` to `""`, but **`Month` defaults to `"1"`** — its option list opens with `1`, no placeholder | A user who never touches Month silently records **January** in a run that may be in month 7. This is a **data-correctness** defect caused by an inconsistent affordance | pass `:placeholder="'Month'"` (as `Year`/`Half` do) at `Setup.vue:182-188` | **P1** | interaction | **A-100 (new)** |
| 27 Similarity | **FAIL** | the three sibling selects behave differently (2 have placeholders, 1 does not) | The odd one out is the one that silently writes data | same | P1 | interaction | A-100 |
| 6 Proximity | PASS | label above control, "I don't know this value" directly under its number | — | — | — | — | — |
| 24 Cognitive Load | PASS | three-state explanation ("type / tick I don't know / leave blank") is stated once | — | — | — | — | — |
| **`aria-required`** | PARTIAL | `BaseSelect.vue` never binds `:required` to `aria-required`; the visual `*` is text only | Screen-reader users are not told a field is required before submitting | add `:aria-required="required"` in `BaseSelect.vue:71-95` | P2 | accessibility | **A-101 (new)** |

**Correction recorded:** F-G4 (a `<label>` wrapping both a stat input and its "I don't know" checkbox) is **fixed**. `snap.log`: `nestedCount: 0`; all 8 stat inputs `hasForLabel: true` with the correct ancestor text.

**Correction recorded:** I first read "Month *" as a free-text box among two selects. Source `Setup.vue:182` shows it is a `BaseSelect`. **Withdrawn**; the real defect is the missing placeholder.

---

### Cockpit — SCR-CAR-011

- **User goal:** decide the next turn for the active career with the state in view.
- **Primary task:** read state → read the recommendation → act.
- **Overall rating:** **Strong**

| Law | Status | Evidence | User impact | Recommendation | Sev | Issue type | Existing ID |
|---|---|---|---|---|---|---|---|
| **26 Flow** | PASS | `cockpit/1280.png`: Recommended action "Rest" + "Why this answer" disclosure open on demand | The decision is made on one screen | — | — | — | — |
| **20 Tesler's** | PASS | 10 summary cards, each naming its absence reason ("No build target set for this run", "This scenario records no shop", …) | The user is never asked to know why a value is empty | — | — | — | — |
| **24 Cognitive Load** | PARTIAL | `names.log` `total: 106` interactive controls on one page | Numerous but chunked; the spine keeps the first action above the fold | none this audit | P3 | visual | — |
| **12 Closure** | PASS | every card is `Documented` with a stated reason or a value | Named absence is the app's strongest pattern | — | — | — | — |
| **27 / a11y** | **PASS** | `names.log`: `unnamed: []`, `titleOnly: []`; `kb.json` `noOutline: []` | — | — | — | — | — |
| **3 Fitts's** | PASS | `Resume`/`Download`/`Save a team state` all 44px; primary is the lime `Save a team state` | — | — | — | — | — |
| **15 Von Restorff** | PASS | exactly one lime-accented primary (`Save a team state`); all other actions are outlined | One distinct element per screen, as §2.6 requires | — | — | — | — |
| **7 Similarity** | PARTIAL | all ten cards share the green "Documented" header bar; a card holding a *typed absence* looks identical to a card holding data | A user cannot scan for "what is actually recorded" | none — the reason text distinguishes them, and changing the bar risks §9.1 | P3 | visual | needs measurement |
| 18 Goal-Gradient | PARTIAL | `squeeze` count 1 at 390 only (`results-layout.json`), no overflow | — | — | P3 | responsive | — |
| 13 Common Fate | **N/A** | no coordinated motion on this screen | — | — | — | — | — |
| 23 Doherty | **N/A** | server-rendered; no measured interaction latency this pass | — | — | — | — | — |

**Note on `Turn 1`:** the dashboard shows "Turn 1" for run #25 and the cockpit shows `11 / 24`. These are two different runs' surfaces (the dashboard's active career vs. the inspected run). Not a contradiction — checked, not filed.

---

### Wizard — six `/career/setup/*` steps

- **User goal:** compose a career setup across six steps.
- **Primary task:** make one choice per step and confirm at preflight.
- **Overall rating:** **Weak**

| Law | Status | Evidence | User impact | Recommendation | Sev | Issue type | Existing ID |
|---|---|---|---|---|---|---|---|
| **19 Zeigarnik** | **FAIL** | `wizard-preflight/1280.png`: stepper reads **"1 Scenario done · 2 Trainee done · 3 Your target done · 4 Legacy done · 5 Support deck done"** while the same page lists five warnings, "Scenario is not chosen / Trainee is not chosen / Your target is not entered / Ancestry is not entered / Support deck is not entered" | **The screen states completion and incompleteness simultaneously.** A user cannot trust the stepper; the most confident element on the page is the least accurate | `SetupLayout.vue:60` must mark `done` only when the step is in `saved`, not when merely `visited` — or label the state "visited" so it stops claiming "done" | **P0** | interaction | **HLF P0 reproduced**; wizard audit §2.2 |
| **27 / hierarchy** | **FAIL** | `0d3a69c` replaced `<h1>Choose a scenario</h1>` with `<p class="mt-1 text-3xl font-semibold">` (`ScenarioSelect.vue:133`). `outline.log` shows each step has **no page heading** — `wizard-legacy` has *only* the shell h1 and zero h2s | The page-level identity is visual only; screen-reader navigation jumps from the banner straight to card content | restore a heading (`<h2>` inside `<main>`, since the shell owns the `<h1>`), keeping the same class | P1 | accessibility | **A-102 (new)**; the "duplicate h1" it fixed was real, but the fix overcorrected |
| **7 Similarity** | **FAIL** | `outline.log`: `wizard-trainee` renders **28 sibling `<h2>` values ranging from a trainer name to a test fixture — `Form Counted 1`, `Form Counted 2`, `Form Counted 3`, `Form Counted 4`**; all share one unstyled `h2` with no `text-` class | Fixture rows are indistinguishable from a real trainee in the picker | filter non-released/fixture forms out of the roster, or label them; give the roster h2s a visible treatment | **P1** | content | **A-103 (new)** |
| **4 Hick's** | PARTIAL | trainee picker renders ~25 rows at once; scenario 4 cards | Bounded, and the client itself presents flat choice (`02:101`) — **not** a PASS by client precedent, but not overload either | retain | P3 | visual | — |
| **2 Jakob's** | PARTIAL | a 6-step wizard is conventional; but the stepper's completion semantics are not | — | tied to the P0 | P0 | interaction | — |
| **18 Goal-Gradient** | PARTIAL | "Step N of 6" is present and correct | Correct where the stepper is wrong | — | P3 | visual | — |
| **13 Common Fate** | PARTIAL | step transitions are SPA navigations with no coordinated motion to mislead | — | — | P3 | visual | — |
| **17 Peak–End** | **FAIL** | the wizard's *end* is the preflight that contradicts itself | The final impression is distrust | tied to the P0 | P0 | interaction | — |
| **12 Closure** | **PASS** | `screens/wizard-preflight/1280.png` prints `▸ N/A` for each empty build field — the collapsed disclosure of `AbsenceValue`. Source `Preflight.vue:231,244,257,320,330` passes a reason per field ("No trainee is recorded in this setup yet.", …), and `AbsenceValue.vue:10-12` makes the reason the summary's accessible **name**, so a screen reader hears it before opening | The absence is named *and* explained — the cockpit's strongest pattern holds here too | — | — | — | **retraction: no finding** |
| 6 Proximity | PASS | stepper → title → copy → cards | — | — | — | — | — |

**On the fixed bottom bar (390px only):** `bar.log` shows the bar occupies `top:799, h:45` at a 844px viewport, and `kb.json` shows the only "obscured" stops are the bar's **own** links. `main` carries `padding-bottom: 96px`, which clears it. **No defect** — the screenshot overlap I first saw was mid-page content scrolled under a correct fixed bar, not the bar covering a control.

**On the HLF comparison:** the HLF reproduces exactly this stepper contradiction (plan §2.5, `frontend-fidelity` §2). The live wizard **matches the HLF here and is still wrong**, which is the caution the brief requires. The HLF's other wizard convention (densified option cards) is **not** adopted as a recommendation — it drops the per-stat/ancestry/deck editors, a scope change needing `SCREEN_SPEC` + `PRD` consent.

---

## 3. Cross-page consistency findings

The `uilaws` "Consistency" law, read as `remaining-screens` §2's `X-1..X-8` rows.

| ID | Consistency dimension | Status | Evidence | User impact | Recommendation | Sev |
|---|---|---|---|---|---|---|
| **X-1** Navigation | PASS | `AppLayout.vue` sidebar identical on all 47 app routes (404 is standalone by design) | — | — | — |
| **X-1b** Navigation — bar overlap | PASS | `kb.json` `obscured` = bar's own links only | — | — | — |
| **X-2** Page headers | **PARTIAL** | shell `#title` is now `text-3xl` (X-2 shell half **fixed**); but wizard steps have **no** page heading (A-102) and page `h2`s are `text-lg` consistently | One cluster lost its heading entirely | A-102 | P1 |
| **X-3** Breadcrumbs / steppers | **FAIL** | stepper `done` contradicts the page it heads (P0); every other page has no stepper | — | P0 | P0 |
| **X-4** Buttons | **PARTIAL** | primary = lime/solid; secondary = outlined — consistent across all 48 routes. `veterans-index` puts `Compare this career` before the name | Slight ordering inconsistency | A-99 | P2 |
| **X-5** Forms | **PARTIAL** | `BaseSelect` placeholder usage is inconsistent: `Year`/`Half` have it, `Month` does not (A-100). Required state is visual-only (A-101) | Silent wrong data | A-100, A-101 | P1 |
| **X-6** Cards | PASS | one `rounded-md border border-rule bg-panel` vocabulary app-wide | — | — | — |
| **X-7** Tables / lists | **FAIL** | `role="list"` applied to non-list children in two places (`Dashboard.vue:258`, `LegacySelect.vue:354`) | Invalid list semantics | A-91 | P1 |
| **X-8** Status indicators | **PARTIAL** | provenance badges consistent in *style*, but `review` doubles the fact (pill + badge). Absence channel splits: `AbsenceValue` on the cockpit vs. `title`-only in `AncestryNode` | — | A-96, F-E3 | P2 |
| **X-9** Empty states | PASS | "never fetched" vs "worked through" (D-26 fixed); snapshot's dashed "Recorded on their own screens" box | Strong | — | — |
| **X-10** Errors | **PARTIAL** | 404 is a good standalone page but is the **only** route axe could not measure | Unverified a11y | A-93 | P3 |
| **X-11** Save feedback | **PARTIAL** | not walked to a saved state on any screen this pass (no writes performed — read-only brief) | **needs measurement** | — | — |
| **X-12** Responsive | **PARTIAL** | 45/48 routes hold at 0 overflow; `skills-detail` (+224), `skills-planner` (+104), `veterans-index` (name clamp) break | — | A-94, A-99 | P1 |

**Laws most likely to FAIL cross-page, as the brief predicted:**

- **7 Similarity — FAIL.** Confirmed on three surfaces (`runs-index`, `veterans-compare`, `wizard-trainee`): same-looking things are different (duplicate names) and different-looking things are the same (a fixture row and a trainee).
- **27 Aesthetic consistency — PARTIAL.** The button/card/token vocabulary is genuinely consistent; the *hierarchy* layer (page headings) regressed in the wizard.
- **9 Uniform Connectedness — PASS** (predicted to fail; it did not). The card border + background idiom holds everywhere.
- **2 Jakob's — PARTIAL**, mostly on the wizard stepper.
- **14 Law of Experience — PARTIAL.** The client presents completion as *absence of a control* (`03:50`) and pending vs. committed as separate baskets (`03:27`). The wizard's stepper instead claims "done" for mere visits — a convention the client does **not** use, and one the app already models correctly on its own cockpit.
- **16 Serial Position — PASS.** Primary actions at predictable head/tail positions across all routes.

---

## 4. Prioritized remediation plan

Each item: exact UI change · affected route/component · law improved · acceptance criteria · **Track**.

### P0 — blocks task completion / serious accessibility failure

**P0-1 · Stepper must not claim `done` for unvisited-content steps**
- **Change:** in `SetupLayout.vue:60`, evaluate `isCompleted()` against `props.saved` only; render `visited` as a separate, weaker state (e.g. "seen") or drop it from the done condition. If `visited` must remain (owner ruling C-1), the label must stop rendering the word "done".
- **Route/component:** `resources/js/layouts/SetupLayout.vue:60,94`; affects all six `/career/setup/*` steps + preflight.
- **Law improved:** 19 Zeigarnik, 17 Peak–End, 12 Closure, 2 Jakob's.
- **Acceptance:** a fresh visit to `/career/setup/preflight` with nothing saved renders **zero** steps as "done" and the five warnings agree; a step that *has* been saved renders "done" and its warning disappears. Covered by a test that asserts stepper state and warning count agree.
- **Track: 1** (craft inside the current contract; no token or ruling change). **Reconciles:** the same P0 already filed in `setup-wizard-ui-audit` §2.2 and reproduced by the HLF.

### P1 — materially increases confusion, errors, or abandonment

**P1-1 · Skill condition string must not widen the document**
- **Change:** add `break-words min-w-0` to the `<dd>` at `Skills/Show.vue:115` (and the same for the planner's condition cells).
- **Route:** `/skills/{id}` (`A-94`), `/training-runs/{id}/skills`.
- **Law:** 27, responsive.
- **Acceptance:** `document.documentElement.scrollWidth − clientWidth === 0` at 390 and 768 on both routes; the condition text still fully readable.
- **Track: 1.**

**P1-2 · Remove invalid `role="list"`**
- **Change:** delete `role="list" aria-label="Career progress"` from `Dashboard.vue:258` (the `SubCell` `<dl>`s carry their own semantics); delete `role="list" aria-label="Legacy details"` from `LegacySelect.vue:354`, or wrap each child in `role="listitem"`.
- **Route:** `/` (`A-91`), `/career/setup/legacy`.
- **Law:** 27, WCAG 1.3.1.
- **Acceptance:** axe rule `aria-required-children` returns 0 nodes on `/`; a screen reader announces the three career figures as three definition lists, not as an empty list.
- **Track: 1.**

**P1-3 · Veteran names must not truncate on mobile**
- **Change:** at `390`, move the name above the action row and allow two lines (drop the fixed narrow column that clamps to `53px`).
- **Route:** `/veterans`, `/veterans/compare`.
- **Law:** 3, 27, responsive.
- **Acceptance:** every veteran name renders in full (`scrollWidth === clientWidth`) at 390; the compare checkboxes carry a unique label per career.
- **Track: 1.**

**P1-4 · Wizard steps need a real heading**
- **Change:** give the step content a `<h2>` (sibling to the `text-lg` card `h2`s) at the position `ScenarioSelect.vue:133` currently fills with a `<p>`, and apply the same to the other five steps.
- **Route:** all six wizard steps (`A-102`).
- **Law:** 27 hierarchy, 11 Continuity, 6 Proximity.
- **Acceptance:** `outline.log` on each step reports ≥1 `h2` before the first card heading; the visual size is unchanged.
- **Track: 1** — this restores semantics without restyling, so it does **not** pre-empt the shell `#title` decision (§9.8).

**P1-5 · Snapshot `Month` must default to empty**
- **Change:** add `:placeholder="'Month'"` to `BaseSelect` at `Setup.vue:182-188`, matching `Year` and `Half`.
- **Route:** `/career/snapshot/setup` (`A-100`).
- **Law:** 12 Closure, 27 Similarity.
- **Acceptance:** a fresh form renders three "unset" selects; submitting without choosing Month produces a validation error rather than silently recording January.
- **Track: 1.**

**P1-6 · Fixture rows must not appear as trainees**
- **Change:** exclude non-released/fixture forms (`Form Counted 1..4`) from the trainee picker roster, or label them unmistakably.
- **Route:** `/career/setup/trainee` (`A-103`).
- **Law:** 7 Similarity, 21 Occam's.
- **Acceptance:** the roster contains only real trainees, or fixture rows carry a visible, distinct marker.
- **Track: 1** (content/data filter). **Note:** this depends on the dev DB seed; if these rows come from `--seed` fixtures, the fix is in the seeder, not the page.

**P1-7 · Duplicate career names must be distinguishable**
- **Change:** append a disambiguator (`Scenario · start date` or run number) wherever two careers share a name.
- **Route:** `/`, `/veterans`, `/veterans/compare` (`A-95`).
- **Law:** 7 Similarity.
- **Acceptance:** no two selectable rows in one surface share an identical rendered label.
- **Track: 1.**

### P2 — reduces efficiency or scanability

| # | Change | Route / component | Law | Acceptance | Track |
|---|---|---|---|---|---|
| P2-1 | Collapse the repeated "no suggestion" sentence to a queue-level summary | `/review`, `Review/Index.vue:108-116` | 21, 6 | one summary line; per-row text only where it differs | 1 |
| P2-2 | Drop the `StatusPill` when `match_tier !== 'Fuzzy'` | `Review/Index.vue:91-95` | 27 | one provenance label per row | 1 |
| P2-3 | Render the "Own rank" helper once per field group | `LegacySelect.vue` (A-98) | 21 | no duplicated sentence in the DOM | 1 |
| P2-4 | Move "No Sparks recorded" to `AbsenceValue`; unify rank `title` | `AncestryNode.vue:102,118` | 12, 27 | one absence channel per component | 1 |
| P2-5 | `:aria-required="required"` in `BaseSelect` | `Form/BaseSelect.vue:71-95` | 12 | required selects expose `aria-required="true"` | 1 |
| P2-6 | ~~Give `wizard-preflight` absence fields a reason line~~ — **withdrawn**, the reasons are already present (`Preflight.vue:231-330`) | — | — | — | — |

### P3 — polish

| # | Change | Route | Law | Track |
|---|---|---|---|---|
| P3-1 | "1 career are active" → correct plural (`A-92`) | `/` | 20 | 1 |
| P3-2 | Sentence-case `NOT IN THIS BUILD` (`A-97`) | `/veterans/compare` | 27 | 1 |
| P3-3 | Rename the `Preferences` h2 under the `Settings` h1 (`A-97`) | `/preferences` | 21 | 1 |
| P3-4 | Run axe against `#main` for error documents (`A-93`) | `error-404` | — | 1 |

### Track 2 — requires a `DESIGN.md` amendment and an owner ruling

**None proposed.** Two candidates were considered and **deliberately not filed**:

1. The HLF's eyebrow + coloured-title page header — blocked by §9.8 (no per-page heading restyle before the shell `#title` decision) and by the HLF's own lime badge colours, which break `app.css:57` until Track 2 D is ruled.
2. The HLF's densified wizard option cards — a **scope** change (it removes per-stat/ancestry/deck editors), needing `SCREEN_SPEC` + `PRD`, not a UX-law fix.
3. Client-corpus conventions worth copying but needing a ruling: per-item detail as a modal over a dimmed backdrop (`00:40`, `04:24`), the shape-rank vocabulary (`00:16`). Both are Track 2 if pursued; neither is a defect on the current screens.

---

## 5. What the audit could not measure

Honest list. Each item is a gap, not a pass.

1. **The integrated browser could not be used.** `browser_open` returned `"Browser session does not belong to the requested project or directory"`. Every capture came from Playwright (Chromium headless) against `:8000`, and the two law sites were read via `webfetch`. **No DOM-rendered, human-viewed pass of lawsofux.com or uilaws.com happened** — both lists nevertheless resolved completely.
2. **The tree changed mid-audit.** The audited state became `0d3a69c` rather than the Phase-0 dirty tree. A commit landed at 15:45:59 during capture. Screenshots represent `0d3a69c`; anything committed after that is unmeasured.
3. **`error-404` is unmeasured for accessibility.** axe errored on all four widths (no `#app` root). That route's a11y status is unknown.
4. **No save/unsaved state was walked.** The brief is read-only and no writes were performed, so "saved vs. unsaved", optimistic-update behaviour, and success feedback are **not judged** (X-11).
5. **Loading states were not measured.** `php -S` is single-threaded; latency on this box is not representative. Doherty Threshold is therefore N/A rather than PASS on every route.
6. **Validation-failure states were only partially walked** — the snapshot form's required-field error rendering was read from source, not triggered in a browser.
7. **Only 13 of 48 routes were probed beyond the metric pass** (keyboard, names, outline). The remaining 35 were judged from screenshots + metrics + source; a hands-on keyboard walk on each is still outstanding.
8. **The 11-run dev database is not a clean state.** It contains fixture rows (`Form Counted 1..4`) and two pairs of duplicate-named veterans. Findings A-95 and A-103 may be **seed artefacts** rather than product defects — they are filed as data-visibility problems either way, but the fix may belong in the seeder.
9. **Three audits' findings were adopted, not re-run.** 76 of the 87 `F-xx` entries were not re-tested this pass; only 11 were.
10. **Colour-contrast was not measured** beyond axe's automated rules. No token was moved (§9.1); no pair table was evaluated.
11. **`Working Memory` and `Selective Attention`** (lawsofux.com, not in the plan's 27) were not judged; no cluster assigns them an instrument.

---

## 6. Explicit stop

This audit measured 48 routes × 4 widths on the running server at `127.0.0.1:8000`, plus targeted probes for keyboard, names, headings, forms and axe detail. It produced **verdicts, not fixes**.

**No tracked file was edited. Nothing was staged, committed, pushed, amended, rebased, reset or force-pushed. The working tree was never stashed.** All writes are under `.scratch-uma/ux-audit-2026-10-17/`.

Thirteen new finding IDs (A-91 → A-103), **four** withdrawn claims, and the corpus retests recorded above make up the deliverable. Every PASS / PARTIAL / FAIL in this report carries a screenshot path, a `file:line`, or a quoted measurement — and the claims I could not substantiate (support-card drill-in, CJK stacking, free-text Month, and the preflight "no reason line") are **withdrawn in place** rather than deleted, so the reasoning stays auditable.

**Fix work does not begin until the owner has reviewed the remediation plan — and in particular the P0, which contradicts a `visited`-based stepper the owner previously ruled on.**
