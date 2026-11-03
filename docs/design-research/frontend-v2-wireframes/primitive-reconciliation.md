# Primitive reconciliation map (frontend-v2-wireframes → app tokens)

This file holds primitive-level evidence (§1–§6) and component-level classification (§7).

**Status: re-derived 2026-10-22 in the D1 correction pass (amendment A27).** Every figure below was
read from `docs/design-research/frontend-v2-wireframes/assets/shell.css` and `resources/css/app.css`
on `master` at `15f7ee1b`, both files git-clean at the time of reading. **Nothing here is transcribed
from a chat transcript**; the earlier E-pass map existed only in conversation and was not a file. Where
the previous pass reported a figure, it is reproduced only if re-derivation gave the same number.

**Method.** Both stylesheets were parsed by line range, not by brace matching: `app.css` `@theme static`
is lines 24-304 and its `html[data-theme='dark']` block 316-434; `shell.css` `:root` is 8-51 and its
dark block 68-92. Declarations were matched with `--name: value;` inside those ranges; each line is
scanned with a global match so multi-property lines are not truncated. Sanity check performed: both
files report `page` = `#08070A` in dark, so the two dark blocks are aligned before any comparison.
Contrast ratios are computed with the WCAG 2.1 relative-luminance formula on the declared hex pair;
they are **arithmetic, not rendered measurements**.

**What this file is not.** It is not `PARITY.md`. The per-component parity classification is a
separate artefact deferred to pass D2 (`ADR-0028` D4). This file holds only the primitive → token
relationship that Phase 2 needs.

## 1. Token-layer facts

| unit | app (`app.css`) | set (`shell.css`) |
|---|---|---|
| line count | 629 | 1,126 |
| distinct custom-property names | **93** (81 `--color-*`, 12 non-colour) | **64** (54 in `:root`, 51 in dark) |
| declaration sites in the file | **159** (93 light + 66 dark redeclarations) | — |
| `--color-*` declarations, whole file | **146** = 81 light + 65 dark | — |

**Namespace.** The app declares `--color-page`; the set declares `--page`. Comparing the raw sets
returns **1** shared name (`--shadow-overlay`). After stripping the app's `--color-` prefix: **52
shared**, **12 set-only**, **41 app-only**.

**The 12 set-only names:** `--rule-soft`, `--r-sm`, `--r-md`, `--r-lg`, `--r-xl`, `--r-pill`,
`--rail-w`, `--rail-w-collapsed`, `--shell-compact`, `--shell-standard`, `--shell-wide`, `--shell`.
The five radii are renames of `--radius-*` (same values). The four shell measures belong to
`AppLayout.vue`. **`--rule-soft` is the only genuinely absent token, referenced 23 times.**

**Theme agreement.** Of the 52 shared names, **39 are byte-identical in light**. Ten of the remainder
are `--tint-*`/`--line-*`, which the set declares **only in its dark block**, so `.band-*` has no tint
in the set's light theme. The five dark `--line-*` values disagree with the app's. The set's
`primary-action` is a literal (`#2255FF`) where the app aliases `--color-brand-chrome`, so the set's
dark primary fill is the light one.

### 1.1 The three shared names whose light values differ and are not `--tint-*`/`--line-*`

The paragraph above accounts for 10 of the 13 differing shared names (the tints and lines the set
declares only in dark). **Three were previously left unnamed.** All three are **token-level
observations, not family concerns** — no primitive in the 21 families resolves to any of them — but
each is a silent divergence on port, so each needs a name and a verdict.

| # | shared name | app value (light) | set value (light) | does a family resolve to it? | verdict |
|---|---|---|---|---|---|
| 1 | `primary-action` | `var(--color-brand-chrome)` → `#2255FF` | literal `#2255FF` | **no** — only `.btn-primary`, which is shell chrome outside the 21 | **fidelity defect in the set** (see 1.2) |
| 2 | `on-primary-action` | `var(--color-on-brand-chrome)` → `#FFFFFF` | literal `#FFFFFF` | **no** — same | **RE-SPELL** if ever ported: alias, don't literal |
| 3 | `shadow-overlay` | `0 4px 12px rgb(0 0 0 / 0.18)` | `0 4px 12px rgb(0 0 0 / .18)` | **no** — `--shadow-overlay` is an elevation token, not a primitive colour | **equivalent** — same value, notation only (`0.18` vs `.18`); no action |

**Why the two aliases matter even though they agree in light.** In light the app's alias and the set's
literal resolve identically, so a ported primitive would look right and the divergence would hide. In
**dark** they separate (see 1.2). A shared name whose light values agree is therefore not evidence
that the pair is safe — which is the general form of the warning: **the promotion rule takes the APP's
value**, so where the set and the app disagree the ported primitive will not look like the set, and
where they agree only in one theme the divergence appears only in the other.

### 1.2 The set's dark primary action is its light one — a fidelity defect

`shell.css`'s `:root` sets `--primary-action:#2255FF` and its **dark block never redeclares it**, so
in dark the set's primary fill stays the light blue. The app does not do that: `--color-primary-action`
is an alias of `--color-brand-chrome` (`app.css` light `#2255FF`, dark **`#1A3FCC`**), so the app's
dark primary is a genuinely different, darker blue.

**Verdict: a fidelity defect in the set, routed to D2's divergence list — not a family row.** It is
not one of the 21 portable families because `.btn-primary` is shell chrome the app already owns (see
§2.1 below), so the Phase 2 port has nothing to re-spell. What it does mean is that the set's dark
theme misrepresents the app's primary action, and any screenshot-or-diff comparison run against the
set in dark will report a difference the app cannot close. It must not survive as only a colour
literal in §5's inventory, which is why it is recorded here with a route.



## 2. The 21 portable primitive families

Bucket counts are **exclusive at the family level**: each family appears once. Promoted tokens are a
*value*-level count belonging to one family, and are not added to the family total.

| # | family | verdict | notes |
|---|---|---|---|
| 1 | `.card` | **CONFLICT** | border `--rule-soft` absent + 1.37:1; `FQ-01` |
| 2 | `.tile` `.tiles` | RE-SPELL | border → `--color-rule`; padding to scale |
| 3 | `.pill` + 5 variants | RE-SPELL | all colour pairs reuse and pass |
| 4 | `.tag` + 2 | RE-SPELL | border → `--color-rule`; 10px type |
| 5 | `.type-chip` | RE-SPELL | border → `--color-rule`; 11px type |
| 6 | `.stat-icon` | RE-SPELL | geometry only; 18px off-scale |
| 7 | `.state` `.empty` | RE-SPELL | colours reuse; padding/measure to scale |
| 8 | `.state-error` | **CONFLICT** | `#fff` on `--risk` = 2.44:1 dark; `FQ-04` |
| 9 | `.skel` | **DROP** | `AGENTS.md` §13 bans decorative skeletons |
| 10 | `.warn` | RE-SPELL | outer border → `--color-rule` |
| 11 | `.note` | RE-SPELL | colours reuse |
| 12 | `.info` | RE-SPELL | outer border → `--color-rule` |
| 13 | `.srow` | **CONFLICT** | border `--rule-soft`; same cause as `.card` |
| 14 | `.pickcard` | **CONFLICT** | `box-shadow` inset ring; `FQ-02` |
| 15 | `.subnav` + hub/item/sep/current | **CONFLICT** | `box-shadow` + 3px outline vs base 2px; `FQ-02` |
| 16 | `.stepper` `.step` `.step-legend` | RE-SPELL | `#fff` → `--color-on-brand-chrome` |
| 17 | `.savebar` | **CONFLICT** | elevation on a non-overlay; `FQ-03` |
| 18 | `.trust-*` | REUSE | every value resolves; all pass |
| 19 | `.mood-*` | REUSE | every value resolves; pass ≥3:1 |
| 20 | `.slot-*` | RE-SPELL | `--ink-faint` on `--sunken` = 2.72:1; **resolution determined** — re-spell to `--ink-muted`, an existing token at 4.57:1. No ruling owed: no new token, no doc amendment |
| 21 | `.stat-*` | REUSE | one family; every colour reuses an existing token (`.stat-sp` = `--color-sp-ink`), and it **additionally contributes 7 promoted token VALUES** — a value-level count, not a 22nd family. Its promotion is the subject of `FQ-05` |

**Totals, stated at two levels so they cannot be confused.**

- **FAMILIES — 21.** Buckets are exclusive, one verdict per family: **6 CONFLICT** (`#1` `.card`, `#8` `.state-error`, `#13` `.srow`, `#14` `.pickcard`, `#15` `.subnav`, `#17` `.savebar`) + **11 RE-SPELL** (`#2`–`#7`, `#10`–`#12`, `#16`, `#20`) + **3 REUSE** (`#18` `.trust-*`, `#19` `.mood-*`, `#21` `.stat-*`) + **1 DROP** (`#9` `.skel`) = **21**.
- **CAUSES — 5 questions.** These families represent **4 distinct house-rule conflicts**, because two pairs share one cause each: `.card` and `.srow` are one cause (`--rule-soft` absent, 1.37:1 — the same missing token, the same fix), and `.pickcard` and `.subnav` are one cause (`box-shadow` selection, plus the 3px outline). Those four are `FQ-01`…`FQ-04`. The fifth question, `FQ-05`, is **not a house-rule conflict**: it is the promotion decision that `.stat-*` forces at value level (7 tokens, and the five-noun collision with `--color-type-*`). So **6 conflict families → 4 causes; 5 causes in total including the promotion question.**
- **PROMOTED VALUES — 7**, all inside family `#21`. Not additive with the family total.

`.band-*` is deliberately **not** a row in this table: it is a *token-level* observation from §1 (the set declares `--tint-*`/`--line-*` only in its dark block, so the bands lose their tint in light), not a primitive family. An earlier revision called it "one of the four REUSE families", which is what made the arithmetic disagree with the table; the family total is 21 and none of the 21 is `.band-*`.

### 2.1 Accounting for the reduction: 107 set selectors → 21 portable families

Phase 2's scope was written as "~40 primitives"; this map accounts for **21 families**. A reconciliation
that silently drops four-fifths of its source cannot be checked, so this section states where every set
selector went. Counted from `shell.css`'s **107 unique top-level class selectors** (measured 2026-10-22,
1,126 lines; the "~120" in the plan is the same figure counted differently).

**The arithmetic, at selector level — 107 = 54 + 50 + 3:**

| bucket | selectors | what it means |
|---|---|---|
| **PORTABLE** — a primitive the app needs and does not have in this form | **54** | groups into the **21 families** of §2 |
| **DUPLICATED** — the app already owns an equivalent component or Tailwind utility | **50** | not ported; the primitive layer would be a second implementation |
| **DROPPED** — decorative scaffold with no product surface | **3** | `proto`, `proto-live`, `mockbar` (the wireframe's own prototype banner) |

| duplicated group | count | selectors | the app owner |
|---|---|---|---|
| Shell chrome | 17 | `app`, `rail`, `rail-brand`, `rail-chevron`, `rail-foot`, `rail-group`, `rail-nav`, `rail-toggle`, `rail-top`, `topbar`, `topbar-in`, `main`, `mbar`, `phead`, `skip`, `wfnav`, `theme-switch` | `AppLayout.vue` + the skip link |
| Layout / grid | 12 | `grid`, `g-2`, `g-3`, `g-4`, `g-main`, `g-side`, `toputils`, `hrow`, `hrow-actions`, `hrow-position`, `canvas`, `track` | Tailwind's grid/flex utilities |
| Buttons | 6 | `btn`, `btn-danger`, `btn-primary`, `btn-quiet`, `btn-sm`, `iconbtn` | `TinyButton` / `.btn` equivalents |
| Typography and utility | 8 | `mono`, `sr-only`, `glyph`, `anno`, `sublabel`, `kbd`, `kbd-on-chrome`, `keybox` | `--font-mono`, `sr-only`, text utilities |
| Data, nav and one-off | 7 | `nav-item`, `pager`, `field`, `filters`, `lin-gen`, `lin-node`, `ancestry` | `Pagination`, `PageHeader`, form primitives, `AncestryNode` |

**At family level the 54 portable selectors group into 21 families**, because a family covers its own
variants and modifiers: `.pill` + its 5 tone classes is one family, `.slot` + `slot-10/12/16` is one,
`.stat-*`'s 8 colour classes are one, `.band-*`'s 5 are one. Of those 21, **6 are CONFLICT, 11 RE-SPELL,
3 REUSE, 1 DROP** (§2), and the DROP is `.skel` — which is why the set-level DROPPED count is **3
scaffold selectors plus 1 portable family**, two different levels that must not be added together.

**So the reduction is accounted for in three ways, each checkable:** 107 selectors → 54 portable + 50
duplicated + 3 scaffold; 54 portable → 21 families (variants collapsed); 21 families → 6 conflict + 11
re-spell + 3 reuse + 1 drop.



## 3. Promotion list (7 tokens, all inside family #21)

| token | light | dark | computed on `raised` |
|---|---|---|---|
| `--color-stat-speed` | `#2F6BC4` | `#7FA8E8` | 5.22:1 light / 6.26:1 dark |
| `--color-stat-stamina` | `#C64B5E` | `#F08A96` | 4.60:1 |
| `--color-stat-power` | `#8A5A32` | `#C99A6B` | 5.85:1 |
| `--color-stat-guts` | `#C4557E` | `#F08BB4` | 4.24:1 |
| `--color-stat-wit` | `#3E6B3A` | `#8CC78A` | 6.23:1 |
| `--color-stat-pal` | `#6E7A45` | `#AEBB84` | 4.63:1 |
| `--color-stat-group` | `#3E7A4A` | `#86C996` | 5.14:1 |

`.stat-sp` (`#0E7490`) is **not** promoted: it is already `--color-sp-ink`. Dark ratios for six of the
seven are **TO-MEASURE** — they need the D-288/G-18 gate.

**Status of those six, as at 2026-10-22 (post-2.0.1).** The gate is no longer hypothetical: it is
implemented and committed at `da15a5ba` — `g18ContractPairs()` carries 31 pairs, `g18GradeBadgeTokens()`
9 badges, the tripwire asserts `toHaveCount(81)`, and no `markTestIncomplete` remains under `tests/`.
So the six dark ratios are **awaiting a run, not awaiting an instrument**. One point stays open and
does not change the six: whether that gate measures **computed styles in a rendered browser** or reads
the **stylesheet's declarations** and computes from them is being established. In either sense the gate
computes the ratio from the resolved values of the declared pair, and in either sense the six below
should be re-recorded from its output rather than from arithmetic. Until that run is quoted, the six
stay **computed, not rendered** — the same status `DESIGN.md` §2.1 gives its own table.

> **Corrected 2026-10-22 (agent-2 absorption pass, A5). The run has happened and the six are still unmeasured — so their status is the third one, not either of the two above.** The run at `da15a5ba` passed: **3 cases, 184 assertions, exit 0, both themes, 0 failures**. It did **not** cover these six, because `stat-*` appears **0 times** as a pair in `DesignTokensTest.php`. The question about the sense of measurement is now settled too — it resolves **computed styles in a real browser** through a probe element, a rendered measurement — so that clause above is no longer open. Net effect on the six: **the instrument exists, the sense is known, and the run cannot reach them.** Their correct status is therefore **"awaiting the pair-list extension (FQ-18), not awaiting a run"**, which is a fourth state this note did not anticipate: a run will not surface them however many times it is repeated, until the pairs are added. Their ratios stay **computed from declared hex**, and each stays **TO-MEASURE** in the rendered sense.

## 4. The five causes behind the conflicts (the FQ register's source)

One row per **cause**, not per family: six families carry four causes, and one question (`FQ-05`) comes from a REUSE family's promotion. Every row carries an FQ number — a cause with no question would be a hole in the register.

| # | cause | families it covers | conflict | FQ |
|---|---|---|---|---|
| 1 | `--rule-soft` does not exist in the app | `.card`, `.srow` | no token; 1.37:1 light / 1.34:1 dark, under the 3:1 a boundary owes | FQ-01 |
| 2 | `box-shadow` used for selection state | `.pickcard`, `.subnav` | house invention per `DESIGN.md` §2.3; plus a 3px focus outline against the base layer's 2px/2px | FQ-02 |
| 3 | elevation on a non-overlay | `.savebar` | `--shadow-overlay` on a sticky bar; §2.3 permits overlays only | FQ-03 |
| 4 | white ink on a themed risk fill | `.state-error` | `#fff` on `--risk` = 10.89:1 light / **2.44:1 dark** | FQ-04 |
| 5 | seven stat colours have no token, and two token families share five nouns | `.stat-*` (REUSE family; 7 promoted values) | promotion decision, not a house-rule violation | FQ-05 |

**`.slot-*` is not in this table.** Its conflict (`--ink-faint` on `--sunken` = 2.72:1) has a resolution already determined by an existing token — re-spell to `--ink-muted`, 4.57:1, no new token and no `DESIGN.md` amendment — so it is a RE-SPELL, not a question. Filing it as a question would add a cause with only one defensible option.

### 4.1 Each cause is a named assertion — the register is executable

The gate is live and committed at `da15a5ba`, so each cause above has a **measurable acceptance
criterion**: the owner's answer to an FQ is a proposition about the stylesheet, and the named assertion
passing is the proof it was right. This table is the link between the cause and its instrument.

| cause | FQ | the assertion that proves the answer | the pair | state today |
|---|---|---|---|---|
| 1 `--rule-soft` absent | FQ-01 | `rule on page` at 3:1, once the soft hairline is gone or promoted | `rule on page` | **passes 3.46:1 / 3.88:1** for `--color-rule`; the set's `--rule-soft` has no token, so it is **not in the pair list at all** — the gate is blind to the failing value |
| 2 `box-shadow` selection | FQ-02 | `pick-line on raised` at 3:1 once the inset shadow is replaced | `pick-line on raised` | **not in the pair list** — a coverage gap, not a failure; the boundary is contracted at 6.24/8.46:1 in `DESIGN.md` §2.1 |
| 3 elevation on a non-overlay | FQ-03 | **not a contrast fact** — the criterion is the ported framework check's `box-shadow` prohibition (rule 34 in §5) | — | n/a; the question must be judged by that rule, and saying so is what keeps it from being a judgement |
| 4 white ink on a risk fill | FQ-04 | `on-risk on risk` at 4.5:1, a pair that cannot exist until FQ-04 mints `--color-on-risk` | `on-risk on risk` | **fails by construction**: `#fff` on dark `--color-risk` = **2.44:1** computed |
| 5 seven stat colours | FQ-05 | seven `stat-* on raised` rows at 3:1 | `stat-* on raised` | **not in the list**; computed 4.24–6.23:1 once minted |

> **Every "state today" cell above is now measured, not hypothesised — 2026-10-22 (agent-2 absorption pass, A4).** The gate was run at `da15a5ba`: **3 cases, 184 assertions, exit 0, both themes, 0 failures**, over 31 text/boundary pairs + 9 grade-badge fills. Green, and green is the confirmation rather than the contradiction: each cause's pair is absent from the list, so the run could not have failed on any of them.
>
> | cause | the cell above said | measured confirmation |
> |---|---|---|
> | 1 `--rule-soft` absent | "not in the pair list at all — the gate is blind" | **confirmed** — `rule-soft` **0 occurrences** in `DesignTokensTest.php`; the run is green, so the blind spot is demonstrated rather than asserted |
> | 2 `box-shadow` selection | "not in the pair list — a coverage gap" | **confirmed** — `pick-line` **0 occurrences**; `pick-line on raised` is not among the 31 pairs |
> | 3 elevation on a non-overlay | "n/a; the question must be judged by that rule" | **unchanged** — still no ported `box-shadow` check exists; no run could reach it |
> | 4 `#fff` on risk | "fails by construction — 2.44:1 computed" | **confirmed as untested** — `risk` **0 occurrences**; the 2.44:1 figure remains arithmetic on declared hex, and the run neither clears nor fails it |
> | 5 seven stat colours | "not in the list; computed 4.24–6.23:1 once minted" | **confirmed** — `stat-*` **0 occurrences** as pairs |
>
> **Consequence for Phase 2, updated.** The baseline the plan told Phase 2 to record **has now been recorded**: 0 failures over 31 pairs + 9 badges, both themes, at `da15a5ba`. It will not change, and re-running will not enrich it, until **FQ-18** extends the list. That makes this table's "executable register" heading currently stronger than its contents: the register is *stated* as executable and is **not yet executable for any of its five causes**.

**The rule these five imply, and it is the reason this table exists:** an FQ is **answered** when its
named assertion passes — not when the owner expresses a preference — and an answer that leaves its
assertion red is **not an answer**. Where the criterion is not a contrast ratio (cause 3), the question
must name its own instrument, and that instrument is its acceptance criterion. A question with neither a
named assertion nor a named instrument is a judgement and has to say so; none of the five is in that
position, which is what makes all five measurable.

**Two consequences for Phase 2.** Its **opening gate run is the register's baseline**: causes 1 and 4
are the two expected failures, and recording them at the head commit is what makes a later green
attributable to a specific ruling rather than to drift. And cause 1's blindness above is itself a finding
worth carrying: the pair list cannot see `--rule-soft` because that name is not declared by `app.css`,
which is precisely **rule 36** (§5) — an unresolved `var()` resolves to nothing and measures nothing.

## 5. Literal inventory — the check's rule set

> **Ownership 2026-10-22 (agent-2 pass / A3): this file owns the numbered rows.** The plan's `### Literal inventory` section carried a second full copy at a different count — 37 rows, because it split this file's **rule 36** into two rows ("no set-only custom property" and "no unprefixed token reference"). **This file's one-row form is the authority**: 36 rows here, 37 there, and the difference is that split, not a missing rule. The plan now cites rows 1–36 here and keeps its copy as a historical record that must not be edited. A change to any row is made **in this file** first.
>
> **The transfer is conditional and currently unmet:** this file is **untracked** in the worktree. Until it is committed, the plan's copy is the only one that travels with the repository, so neither copy may be deleted on the strength of this note. The transfer completes when this file is staged.

**A. Colour literals in rules (21).** The token blocks' own hex is legitimate definition, exactly as
`app.css`'s `@theme static` is; these are the ones inside selectors.

| # | line | value | owner |
|---|---|---|---|
| 1-7 | 96-102 | `#7FA8E8` `#F08A96` `#C99A6B` `#F08BB4` `#8CC78A` `#AEBB84` `#86C996` | dark `.stat-*` brighteners |
| 8 | 190 | `#fff` | glyph ink |
| 9 | 676 | `#fff` | on `--risk` (FQ-04) |
| 10-17 | 793-800 | `#2F6BC4` `#C64B5E` `#8A5A32` `#C4557E` `#3E6B3A` `#6E7A45` `#3E7A4A` `#0E7490` | light `.stat-*` |
| 18 | 1031 | `#fff` | `.step[aria-current] .n` |
| 19 | 1096 | `#fff` | accent pill ink |
| 20 | 1112 | `#fff` | `.wfnav` |
| 21 | 1125 | `#fff` | focus outline on chrome |

*Excluded:* lines 1016 and 1018 contain `#9BE83A` and `#4E7906` **inside a comment** — prose about a
measurement, not declarations.

**B. Radius literals (10).** The declared scale is 6/10/14/16/999; these are outside it.

| # | line | value |
|---|---|---|
| 22 | 259 | `0 3px 3px 0` |
| 23 | 571 | `5px` |
| 24 | 756 | `4px` |
| 25 | 1052 | `2px` |
| 26 | 1104 | `4px` |
| 27-31 | 270, 722, 994, 1009, 1036 | `50%` (a dot; acceptable, no scale step for a circle) |

**C. Duration and easing (1).**

| # | line | value | conflict |
|---|---|---|---|
| 32 | 178 | `transition:grid-template-columns .18s ease` | `.18s` ≠ `150ms`, `ease` ≠ `ease-out`, layout scope not in MOTION 2 (FQ-07) |

**D. Length literals (1 rule, 55 distinct values).**

| # | rule |
|---|---|
| 33 | No literal `px` outside the token block. Measured: **514** occurrences across **55** distinct values. Most frequent: `44px`×44, `1px`×59, `2px`×30, `10px`×30, `12px`×30, `9px`×22, `6px`×21, `20px`×20. The 44 count is the house target floor being honoured, not a defect. |

**E. Two structural rules the inventory implies.**

| # | rule |
|---|---|
| 34 | No `box-shadow` anywhere (4 in the set: lines 558, 912, 971, 1111). |
| 35 | No `outline` wider than the base layer's `2px`/`2px` (`app.css:525-528`). The set's `3px` outlines are at **lines 205, 332, 426, 899, 905** (`.rail-toggle`, `.skip`, `.btn`, `.subnav-hub`, `.subnav-item`) and one more at **738** (`.iconbtn`, 3px in `--on-brand-chrome`). **Corrected 2026-10-22:** this row previously read "lines 1031 and 1125", and both were wrong — **1031** is `.step[aria-current="step"] .n{…color:#fff…}`, a *colour* declaration with no outline at all, and **1125** is `.theme-switch button:focus-visible{outline:2px solid #fff;outline-offset:1px}`, a **2px** outline on a different selector. Two 2px exceptions exist besides 1125: line **518** (`.field .in input/select`, `2px`/`1px` offset). |
| 36 | **No unresolved `var(--…)` in a ported primitive.** Every `var()` must name a custom property `app.css` actually declares, and no set-only name (`--rule-soft`, `--r-*`, `--rail-w*`, `--shell*`) may be referenced. The failure this prevents is silent: an undefined custom property **inherits rather than errors**, so the primitive compiles, renders, and paints its parent's colour — no error, no visual crash, just the wrong value in production. |

**Rule 36's instrument is the one that already exists.** The D-288/G-18 gate in `tests/Feature/DesignTokensTest.php` resolves every declared name off `documentElement` and fails on an empty result (`expect($empty)->toBe([])`), and it fails on a value that is not a colour (`$unparsable`). That is the same machinery rule 36 needs — a resolving reader plus a fail-on-empty assertion — so extending it to the ported primitives' own `var()` references is an extension of an instrument, **not a new dependency and not a new gate**. It should not be implemented as a separate regex check: a regex cannot tell a declared name from an undeclared one, which is precisely the distinction rule 36 exists to make.

## 6. What a ported primitive must not do

1. Reference a set-only name (`--rule-soft`, `--r-*`, `--rail-w*`, `--shell*`) — none resolve.
2. Reference a bare name (`--panel`); the app declares `--color-panel`. An undefined custom property
   **inherits rather than errors**, so the failure is invisible without the D-288 gate — this is
   **rule 36** in §5, and the gate named there is its instrument.
3. Carry a literal colour, length, radius or duration from the list in §5.
4. Use `box-shadow` for state, or a transition outside MOTION 2's five scopes.

## 7. Component parity classification (D2, 2026-10-22)

**Method.** Rendered-signature matching only. The app's components use Tailwind v4 utility
classes backed by `@theme static` tokens, while the set's pages use `shell.css` class
selectors. A row matches when the app produces the same rendered output (same token values,
same geometry, same states) under a different class spelling. Literal-text matching of
class names returns **0 of 97** — verified and recorded as a failure: no class name in
`shell.css` appears verbatim in any Vue SFC's `class=` attribute. The match is on the
**resolved token values and rendered geometry**, not on the class name string.

**Categories (8, fixed).**

| category | meaning |
|---|---|
| `near-copy` | app produces the same rendered output; class names differ but tokens and geometry agree |
| `renamed` | same behavior, different class spelling; the app uses Tailwind utilities or a named component |
| `absent — enhancement` | not in the app; porting would add a capability the app does not have |
| `absent — replacement` | not in the app in this form; replaced by a different component or approach |
| `absent — out of scope` | not in the app; the app's framework (Tailwind, AppLayout) already owns this |
| `dead` | not in the app and must not be — scaffold, mock, or banned by AGENTS.md |
| `app-only — not in the set` | the app has this; the set does not (it is shell chrome the set does not own) |
| `the set is behind` | the set carries a stale version of something the app has moved past |

**Two qualification columns per row.**

1. **Shared tokens depended on** — the `--color-*` (or non-colour) token names both sides
   resolve to, or `none` if the row has no token dependency. For `app-only` rows the set
   does not exist, so the column reads `none`.
2. **Value agreement** — whether the resolved values agree in both themes: `agree`,
   `disagree` (with a pointer to the §1/§3/§4 finding and the FQ that resolves it), or
   `n/a` (one side does not exist). A `disagree` row is a divergence the fix pass closes.

### 7.1 The matrix (107 rows)

| # | shell.css selector | classification | app equivalent | shared tokens | value agreement |
|---|---|---|---|---|---|
| 1 | `.mono` | renamed | font-mono utility | --font-mono | agree |
| 2 | `.sr-only` | renamed | sr-only utility | none | agree |
| 3 | `.topbar` | app-only — not in the set | AppLayout.vue topbar | none | n/a |
| 4 | `.app` | app-only — not in the set | AppLayout.vue grid | none | n/a |
| 5 | `.rail` | app-only — not in the set | AppLayout.vue sidebar | none | n/a |
| 6 | `.rail-top` | app-only — not in the set | AppLayout.vue | none | n/a |
| 7 | `.rail-brand` | app-only — not in the set | AppLayout.vue | none | n/a |
| 8 | `.rail-toggle` | app-only — not in the set | AppLayout.vue | none | n/a |
| 9 | `.rail-chevron` | app-only — not in the set | AppLayout.vue | none | n/a |
| 10 | `.rail-group` | app-only — not in the set | AppLayout.vue | none | n/a |
| 11 | `.rail-nav` | app-only — not in the set | AppLayout.vue | none | n/a |
| 12 | `.nav-item` | app-only — not in the set | AppLayout.vue nav | none | n/a |
| 13 | `.glyph` | app-only — not in the set | NavGlyph.vue | none | n/a |
| 14 | `.rail-foot` | app-only — not in the set | AppLayout.vue footer | none | n/a |
| 15 | `.main` | app-only — not in the set | AppLayout.vue main | none | n/a |
| 16 | `.skip` | app-only — not in the set | AppLayout.vue skip link | none | n/a |
| 17 | `.topbar-in` | app-only — not in the set | AppLayout.vue | none | n/a |
| 18 | `.phead` | renamed | ui/PageHeader.vue | --color-ink-strong, --color-ink-muted | agree |
| 19 | `.canvas` | absent — out of scope | Tailwind layout utilities | none | n/a |
| 20 | `.card` | near-copy | ui/Card.vue | --color-rule, --color-raised | disagree (§4 cause 1: --rule-soft absent, FQ-01) |
| 21 | `.sublabel` | renamed | text-ink-muted utility | --color-ink-muted | agree |
| 22 | `.grid` | absent — out of scope | Tailwind grid utilities | none | n/a |
| 23 | `.g-2` | absent — out of scope | Tailwind gap-2 | none | n/a |
| 24 | `.g-3` | absent — out of scope | Tailwind gap-3 | none | n/a |
| 25 | `.g-4` | absent — out of scope | Tailwind gap-4 | none | n/a |
| 26 | `.g-main` | absent — out of scope | Tailwind layout | none | n/a |
| 27 | `.g-side` | absent — out of scope | Tailwind layout | none | n/a |
| 28 | `.btn` | renamed | Form/BaseButton.vue | --color-chrome, --color-on-chrome, --color-rule | agree |
| 29 | `.btn-primary` | renamed | Form/BaseButton.vue variant | --color-brand-chrome, --color-on-brand-chrome | disagree (§1.2: set dark = light; fidelity defect, not fix-pass) |
| 30 | `.btn-quiet` | renamed | Form/BaseButton.vue variant | --color-rule, --color-ink | agree |
| 31 | `.btn-danger` | renamed | Form/BaseButton.vue variant | --color-risk, --color-on-risk | agree |
| 32 | `.btn-sm` | renamed | Form/BaseButton.vue variant | none | agree |
| 33 | `.pill` | renamed | StatusPill.vue / MoodPill.vue | --color-rule, --color-ink | agree |
| 34 | `.pill-positive` | renamed | StatusPill.vue variant | --color-status-positive, --color-status-positive-ink | agree |
| 35 | `.pill-caution` | renamed | StatusPill.vue variant | --color-status-caution, --color-status-caution-ink | agree |
| 36 | `.pill-neutral` | renamed | StatusPill.vue variant | --color-sunken, --color-ink-muted | agree |
| 37 | `.pill-accent` | renamed | StatusPill.vue variant | --color-brand-accent, --color-brand-accent-ink | agree |
| 38 | `.pill-outline` | renamed | StatusPill.vue variant | --color-rule, --color-ink | agree |
| 39 | `.tag` | renamed | RarityChip.vue / SparkChip.vue | --color-rule, --color-ink-muted | agree |
| 40 | `.trust` | renamed | ProvenanceBadge.vue | --color-ink-muted, --color-ink-faint | agree |
| 41 | `.trust-calculated` | renamed | ProvenanceBadge.vue | --color-ink-strong | agree |
| 42 | `.trust-estimated` | renamed | ProvenanceBadge.vue | --color-ink-muted | agree |
| 43 | `.trust-unknown` | renamed | ProvenanceBadge.vue | --color-ink-faint | agree |
| 44 | `.field` | renamed | ui/FormField.vue | --color-ink, --color-ink-muted, --color-risk, --color-rule | agree |
| 45 | `.pickcard` | renamed | support/CardPicker.vue | --color-pick-line | disagree (§4 cause 2: box-shadow selection, FQ-02) |
| 46 | `.srow` | renamed | SubCell.vue / ui/FormField.vue | --color-rule | disagree (§4 cause 1: --rule-soft absent, FQ-01) |
| 47 | `.track` | absent — replacement | VisitProgressBar.vue | none | n/a |
| 48 | `.keybox` | absent — out of scope | documentation box | --color-rule, --color-ink | n/a |
| 49 | `.tiles` | renamed | grid layout | none | agree |
| 50 | `.tile` | renamed | ui/Card.vue tile variant | --color-rule, --color-raised | agree |
| 51 | `.note` | renamed | ui/Section.vue note | --color-rule, --color-ink | agree |
| 52 | `.warn` | renamed | ui/Section.vue warn | --color-rule, --color-ink | agree |
| 53 | `.info` | renamed | ui/Section.vue info | --color-rule, --color-ink | agree |
| 54 | `.mockbar` | dead | wireframe-only mock bar | none | n/a |
| 55 | `.state` | renamed | ui/AbsenceNote.vue / empty state | --color-rule, --color-ink-muted | agree |
| 56 | `.skel` | dead | AGENTS.md §13 bans decorative skeletons | none | n/a |
| 57 | `.state-error` | renamed | review/CandidateForm.vue error | --color-risk, --color-on-risk | disagree (§4 cause 4: #fff on dark --risk = 2.44:1, FQ-04) |
| 58 | `.mood` | renamed | MoodPill.vue | --color-on-mood, --color-mood-great | agree |
| 59 | `.mood-up` | renamed | MoodPill.vue up | --color-up | agree |
| 60 | `.mood-flat` | renamed | MoodPill.vue flat | --color-ink-muted | agree |
| 61 | `.mood-down` | renamed | MoodPill.vue down | --color-down | agree |
| 62 | `.proto` | dead | wireframe scaffold | none | n/a |
| 63 | `.proto-live` | dead | wireframe scaffold | none | n/a |
| 64 | `.toputils` | absent — out of scope | Tailwind flex | none | n/a |
| 65 | `.iconbtn` | renamed | Form/LinkButton.vue icon | --color-ink, --color-rule | agree |
| 66 | `.kbd` | absent — out of scope | kbd element | --color-ink-muted | n/a |
| 67 | `.kbd-on-chrome` | absent — out of scope | kbd on chrome | --color-on-chrome | n/a |
| 68 | `.stat-icon` | renamed | AptitudeGrid.vue icon | none | agree |
| 69 | `.stat-speed` | renamed | AptitudeGrid.vue speed | --color-stat-speed | disagree (§3: token not yet minted, FQ-05) |
| 70 | `.stat-stamina` | renamed | AptitudeGrid.vue stamina | --color-stat-stamina | disagree (§3: token not yet minted, FQ-05) |
| 71 | `.stat-power` | renamed | AptitudeGrid.vue power | --color-stat-power | disagree (§3: token not yet minted, FQ-05) |
| 72 | `.stat-guts` | renamed | AptitudeGrid.vue guts | --color-stat-guts | disagree (§3: token not yet minted, FQ-05) |
| 73 | `.stat-wit` | renamed | AptitudeGrid.vue wit | --color-stat-wit | disagree (§3: token not yet minted, FQ-05) |
| 74 | `.stat-pal` | renamed | AptitudeGrid.vue pal | --color-stat-pal | disagree (§3: token not yet minted, FQ-05) |
| 75 | `.stat-group` | renamed | AptitudeGrid.vue group | --color-stat-group | disagree (§3: token not yet minted, FQ-05) |
| 76 | `.stat-sp` | renamed | AptitudeGrid.vue sp | --color-sp-ink | agree |
| 77 | `.type-chip` | renamed | AptitudeBadge.vue | --color-rule, --color-ink-muted | agree |
| 78 | `.band-speed` | renamed | AptitudeGrid.vue band | --color-tint-speed, --color-line-speed | disagree (§1: set declares --tint-*/--line-* only in dark; token-level, not fix-pass) |
| 79 | `.band-stamina` | renamed | AptitudeGrid.vue band | --color-tint-stamina, --color-line-stamina | disagree (§1: set declares --tint-*/--line-* only in dark; token-level, not fix-pass) |
| 80 | `.band-power` | renamed | AptitudeGrid.vue band | --color-tint-power, --color-line-power | disagree (§1: set declares --tint-*/--line-* only in dark; token-level, not fix-pass) |
| 81 | `.band-guts` | renamed | AptitudeGrid.vue band | --color-tint-guts, --color-line-guts | disagree (§1: set declares --tint-*/--line-* only in dark; token-level, not fix-pass) |
| 82 | `.band-wit` | renamed | AptitudeGrid.vue band | --color-tint-wit, --color-line-wit | disagree (§1: set declares --tint-*/--line-* only in dark; token-level, not fix-pass) |
| 83 | `.slot` | renamed | support/SupportSlot.vue | --color-ink-muted | disagree (§2 row #20: --ink-faint on --sunken = 2.72:1; RE-SPELL to --ink-muted) |
| 84 | `.slot-10` | renamed | support/SupportSlot.vue variant | --color-ink-muted | agree |
| 85 | `.slot-12` | renamed | support/SupportSlot.vue variant | --color-ink-muted | agree |
| 86 | `.slot-16` | renamed | support/SupportSlot.vue variant | --color-ink-muted | agree |
| 87 | `.hrow` | absent — out of scope | Tailwind flex | none | n/a |
| 88 | `.hrow-actions` | absent — out of scope | Tailwind flex | none | n/a |
| 89 | `.hrow-position` | absent — out of scope | Tailwind flex | none | n/a |
| 90 | `.subnav` | renamed | career/CareerSectionNav.vue | --color-rule | disagree (§4 cause 2: box-shadow + 3px outline, FQ-02) |
| 91 | `.subnav-hub` | renamed | career/CareerSectionNav.vue hub | --color-rule | disagree (§4 cause 2, FQ-02) |
| 92 | `.subnav-sep` | renamed | career/CareerSectionNav.vue sep | --color-rule | agree |
| 93 | `.subnav-item` | renamed | career/CareerSectionNav.vue item | --color-rule, --color-ink | disagree (§4 cause 2, FQ-02) |
| 94 | `.empty` | renamed | ui/Section.vue empty | --color-ink-muted | agree |
| 95 | `.ancestry` | renamed | legacy/AncestryNode.vue root | --color-rule | agree |
| 96 | `.lin-gen` | renamed | legacy/AncestryNode.vue gen | --color-rule | agree |
| 97 | `.lin-node` | renamed | legacy/AncestryNode.vue | --color-rule, --color-ink | agree |
| 98 | `.filters` | absent — out of scope | inline filter form | none | n/a |
| 99 | `.pager` | renamed | ui/Pagination.vue | --color-rule, --color-ink | agree |
| 100 | `.savebar` | renamed | ui/StickyFooter.vue | --color-shadow-overlay | disagree (§4 cause 3: elevation on non-overlay, FQ-03) |
| 101 | `.stepper` | renamed | career/GoalMark.vue / stepper | --color-rule | agree |
| 102 | `.step` | renamed | career/GoalMark.vue step | --color-brand-chrome, --color-on-brand-chrome | agree |
| 103 | `.step-legend` | renamed | career/GoalMark.vue legend | --color-ink-muted | agree |
| 104 | `.mbar` | dead | wireframe-only mock bar | none | n/a |
| 105 | `.anno` | absent — out of scope | inline annotation | none | n/a |
| 106 | `.wfnav` | app-only — not in the set | wireframe-only nav | none | n/a |
| 107 | `.theme-switch` | app-only — not in the set | AppLayout.vue theme toggle | none | n/a |

**Classification counts (107 rows).**

| category | count |
|---|---|
| absent — out of scope | 16 |
| absent — replacement | 1 |
| app-only — not in the set | 17 |
| dead | 5 |
| near-copy | 1 |
| renamed | 67 |
| **total** | **107** |

## Divergences introduced by the fix pass (14 files)

These are the rows whose rendered output the fix pass (steps 2.2 and 2.3) changes. Each
is already named in §4 as a cause with an FQ; this section cross-references the finding to
the fix that closes it. Six `band-*` divergences (§1, token-level only) and one `btn-primary`
divergence (§1.2, fidelity defect in the set) are **not** fix-pass divergences — the fix pass
does not change them — and are excluded from this count.

| # | shell.css selector | app file | divergence | fix |
|---|---|---|---|---|
| 1 | `.card` | ui/Card.vue | §4 cause 1: --rule-soft absent | FQ-01 → border --color-rule |
| 2 | `.srow` | SubCell.vue / ui/FormField.vue | §4 cause 1: --rule-soft absent | FQ-01 → border --color-rule |
| 3 | `.pickcard` | support/CardPicker.vue | §4 cause 2: box-shadow selection | FQ-02 → border --color-pick-line |
| 4 | `.subnav` | career/CareerSectionNav.vue | §4 cause 2: box-shadow + 3px outline | FQ-02 → border 2px |
| 5 | `.subnav-hub` | career/CareerSectionNav.vue hub | §4 cause 2: box-shadow | FQ-02 → outline 2px |
| 6 | `.subnav-item` | career/CareerSectionNav.vue item | §4 cause 2: box-shadow | FQ-02 → border 2px |
| 7 | `.savebar` | ui/StickyFooter.vue | §4 cause 3: elevation on non-overlay | FQ-03 → remove box-shadow |
| 8 | `.state-error` | review/CandidateForm.vue | §4 cause 4: #fff on dark --risk 2.44:1 | FQ-04 → mint --color-on-risk |
| 9 | `.stat-speed` | AptitudeGrid.vue | §3: token not minted | FQ-05 → mint --color-stat-speed |
| 10 | `.stat-stamina` | AptitudeGrid.vue | §3: token not minted | FQ-05 → mint --color-stat-stamina |
| 11 | `.stat-power` | AptitudeGrid.vue | §3: token not minted | FQ-05 → mint --color-stat-power |
| 12 | `.stat-guts` | AptitudeGrid.vue | §3: token not minted | FQ-05 → mint --color-stat-guts |
| 13 | `.stat-wit` | AptitudeGrid.vue | §3: token not minted | FQ-05 → mint --color-stat-wit |
| 14 | `.stat-pal` | AptitudeGrid.vue | §3: token not minted | FQ-05 → mint --color-stat-pal |

## Not verifiable (39 rows)

These rows have `n/a` for value agreement because one side does not exist in the other's
vocabulary — the app owns the shell chrome, or the row is scaffold/dead/out-of-scope.
They are classified, not measured.

- `.topbar` → AppLayout.vue topbar
- `.app` → AppLayout.vue grid
- `.rail` → AppLayout.vue sidebar
- `.rail-top` → AppLayout.vue
- `.rail-brand` → AppLayout.vue
- `.rail-toggle` → AppLayout.vue
- `.rail-chevron` → AppLayout.vue
- `.rail-group` → AppLayout.vue
- `.rail-nav` → AppLayout.vue
- `.nav-item` → AppLayout.vue nav
- `.glyph` → NavGlyph.vue
- `.rail-foot` → AppLayout.vue footer
- `.main` → AppLayout.vue main
- `.skip` → AppLayout.vue skip link
- `.topbar-in` → AppLayout.vue
- `.canvas` → Tailwind layout utilities
- `.grid` → Tailwind grid utilities
- `.g-2` → Tailwind gap-2
- `.g-3` → Tailwind gap-3
- `.g-4` → Tailwind gap-4
- `.g-main` → Tailwind layout
- `.g-side` → Tailwind layout
- `.track` → VisitProgressBar.vue
- `.keybox` → documentation box
- `.mockbar` → wireframe-only mock bar
- `.skel` → AGENTS.md §13 bans decorative skeletons
- `.proto` → wireframe scaffold
- `.proto-live` → wireframe scaffold
- `.toputils` → Tailwind flex
- `.kbd` → kbd element
- `.kbd-on-chrome` → kbd on chrome
- `.hrow` → Tailwind flex
- `.hrow-actions` → Tailwind flex
- `.hrow-position` → Tailwind flex
- `.filters` → inline filter form
- `.mbar` → wireframe-only mock bar
- `.anno` → inline annotation
- `.wfnav` → wireframe-only nav
- `.theme-switch` → AppLayout.vue theme toggle

### §7.2 Component-side re-derivation (D2, 2026-10-23)

**Why a second matrix rather than an edit.** §7.1 above classifies the **set's selectors** (107 rows:
`shell.css` selector → app equivalent). The D2 brief also asks for one row per **app component**. Those
are two views of one classification, and §7.1 is untouched: it remains authoritative for the selector
axis. This section is the component axis, measured after Phase 3 closed.

**Tree at measure time.** `46a154d9` on `master`, `git status --short` clean. Measured by reading the
tree, not by rendering it.

| measure | value | note |
|---|---|---|
| SFCs in tree | **96** | 97 − the two URA files deleted at `681e8b70` = 95, **+ `ui/Flash.vue` added at `ae715f7a`**. The brief's 95 predates the flash extraction; 96 is the tree |
| colour tokens pinned | **89** | 81 base + seven `--color-stat-*` + `--color-on-risk` (Phase 2.2); asserted at `DesignTokensTest.php` `toHaveCount(89)` |
| HFW pages | **56** | 49 base + 7 drawn in Phase 4a |
| page files | **57** | 49 route-served + 8 Cockpit panels |
| browser specs | **77** | `tests/browser/*.spec.ts` |
| Feature files | **203** top-level / **206** recursive | the 3-file delta is `tests/Feature/Schema/` |
| Phase 3 state | **50** re-spelled / **7** accepted under CQ-01 / **39** accepted under CQ-05 | 50 + 7 + 39 = 96 |

> **Corrected 2026-10-24 (fact-check).** Two rows of the table above are superseded by Phase 4b's
> `review-detail` landing, and no dated note beside either flags it. Re-derived on the current tree
> (`002ab54a`) and at the 2.1.0 tag (`87c123e6`): **browser specs = 78**, not 77 — the 78th is
> `tests/browser/review-detail.spec.ts` (`d7801605`) — and **page files = 58**, not 57 — the 58th is
> `pages/Review/Detail.vue` (`065753f0`). Both were 77 and 57 at `e3ac31bd` (2.0.2). The rows stand as
> written.

**Method.** Rendered-signature proxies, in the order the brief fixes them: the **primitive class
families** a component emits (the 35 names the primitive layer declares), and its **aria tokens**
(`aria-label` / `aria-labelledby` / `aria-describedby`) matched against the page that shares the most
with it. Node counts are the element count inside `<template>`. Literal-text matching of class names
against the set returns **0 of 96** — the same failure §7.1 records; it was not retried.

**The discrimination limit, stated up front, because it shapes the counts.** CQ-05 accepted utility-only
spellings, so **50 of the 96 components emit no primitive class at all** — they are token utilities by
ruling. A further **20** emit primitive families but carry no aria token to compare names with. Those
**70 rows are `unclassified`**, each with its reason, rather than guessed into a category. That is the
honest consequence of closing Phase 3 on rules 7 and 8: the axis the D2 brief was written against has
less to bite on than it did on 2026-10-22.

**Classification counts.** `near-copy` **7** · `renamed` **19** · `unclassified` **70** (50 with no
primitive class, 20 with primitives but no aria token) · `app-only — not in the set` **0** ·
`dead` **0**. No component is marked `dead`: the plan's Phase 3 gate note already records that form
controls and layout components were "read and confirmed clean", and an inherited "dead" claim is exactly
what §7.1's disposition rule forbids.

### The matrix (96 rows)

| component | category | rendered signature (primitives · elements) | aria tokens | HFW counterpart | shared tokens | value agreement |
|---|---|---|---|---|---|---|
| `AbsenceValue.vue` | unclassified | — · 4 elements | — | `career-deck.html` | — | n-a |
| `AptitudeBadge.vue` | unclassified | — · 6 elements | — | `career-deck.html` | — | n-a |
| `AptitudeGrid.vue` | unclassified | — · 7 elements | — | `career-deck.html` | — | n-a |
| `ArtworkSlot.vue` | unclassified | — · 4 elements | 1 aria | `career-deck.html` | — | n-a |
| `CapsuleHeader.vue` | unclassified | — · 2 elements | — | `career-deck.html` | — | n-a |
| `EnergyGauge.vue` | unclassified | — · 10 elements | — | `career-deck.html` | — | n-a |
| `Form/BaseButton.vue` | unclassified | — · 2 elements | 1 aria | `career-deck.html` | — | n-a |
| `Form/BaseCheckbox.vue` | unclassified | — · 8 elements | 1 aria | `career-deck.html` | — | n-a |
| `Form/BaseInput.vue` | unclassified | — · 5 elements | 2 aria | `career-deck.html` | — | n-a |
| `Form/BaseRadio.vue` | unclassified | — · 8 elements | 1 aria | `career-deck.html` | — | n-a |
| `Form/BaseSelect.vue` | unclassified | — · 11 elements | 2 aria | `career-deck.html` | — | n-a |
| `Form/BaseTextarea.vue` | unclassified | — · 5 elements | 1 aria | `career-deck.html` | — | n-a |
| `Form/LinkButton.vue` | unclassified | — · 2 elements | — | `career-deck.html` | — | n-a |
| `KpiCard.vue` | unclassified | card · 8 elements | — | `career-deck.html` | card | n-a |
| `MoodPill.vue` | unclassified | mood · 3 elements | — | `cockpit-grand-concert.html` | mood | n-a |
| `NavGlyph.vue` | unclassified | — · 2 elements | — | `career-deck.html` | — | n-a |
| `ProvenanceBadge.vue` | unclassified | — · 1 elements | — | `career-deck.html` | — | n-a |
| `ProvenanceMark.vue` | near-copy | card pill pill-neutral · 5 elements | 1 aria | `career-legacy.html` | card pill pill-neutral | n-a |
| `RarityChip.vue` | unclassified | — · 2 elements | 1 aria | `career-deck.html` | — | n-a |
| `ResourceStrip.vue` | unclassified | card · 12 elements | — | `career-deck.html` | card | n-a |
| `SkillRow.vue` | unclassified | pill pill-neutral · 7 elements | — | `career-legacy.html` | pill pill-neutral | n-a |
| `SparkChip.vue` | unclassified | — · 1 elements | — | `career-deck.html` | — | n-a |
| `SpiritBurstRoster.vue` | renamed | card · 7 elements | 1 aria | `career-deck.html` | card | n-a |
| `StatusPill.vue` | unclassified | pill · 1 elements | — | `career-deck.html` | pill | n-a |
| `SubCell.vue` | unclassified | card · 8 elements | — | `career-deck.html` | card | n-a |
| `TeamRacePanel.vue` | near-copy | card · 15 elements | 1 aria | `career-scenario.html` | card | n-a |
| `TeamRankGauge.vue` | renamed | card · 14 elements | 1 aria | `career-deck.html` | card | n-a |
| `TraineeCombobox.vue` | renamed | card · 10 elements | 3 aria | `career-deck.html` | card | n-a |
| `VeteranRow.vue` | unclassified | pill pill-neutral · 20 elements | — | `career-legacy.html` | pill pill-neutral | n-a |
| `VisitProgressBar.vue` | unclassified | — · 2 elements | 1 aria | `career-deck.html` | — | n-a |
| `career/AcquisitionStatusEditor.vue` | renamed | card · 29 elements | 1 aria | `career-deck.html` | card | n-a |
| `career/ActionGrid.vue` | renamed | card · 11 elements | 1 aria | `career-deck.html` | card | n-a |
| `career/AdvisorRail.vue` | renamed | card · 4 elements | 1 aria | `career-deck.html` | card | n-a |
| `career/CareerSectionNav.vue` | near-copy | card · 16 elements | 2 aria | `career-deck.html` | card | n-a |
| `career/CareerStatePanel.vue` | renamed | card · 29 elements | 1 aria | `career-deck.html` | card | n-a |
| `career/CareerTimeline.vue` | near-copy | card · 56 elements | 1 aria | `timeline.html` | card | n-a |
| `career/ChoicesSoFar.vue` | unclassified | — · 8 elements | — | `career-deck.html` | — | n-a |
| `career/CockpitIdentityCard.vue` | renamed | card · 38 elements | 1 aria | `career-deck.html` | card | n-a |
| `career/EventCard.vue` | unclassified | card · 22 elements | — | `career-deck.html` | card | n-a |
| `career/GoalMark.vue` | unclassified | — · 3 elements | — | `career-deck.html` | — | n-a |
| `career/NextDecision.vue` | renamed | card · 46 elements | 1 aria | `career-deck.html` | card | n-a |
| `career/ObjectiveRow.vue` | unclassified | — · 11 elements | — | `career-deck.html` | — | n-a |
| `career/RaceCard.vue` | unclassified | card · 53 elements | — | `career-deck.html` | card | n-a |
| `career/RacePlanList.vue` | renamed | note · 11 elements | 1 aria | `career-deck.html` | note | n-a |
| `career/RecordedActionCard.vue` | unclassified | — · 35 elements | 1 aria | `career-deck.html` | — | n-a |
| `career/RunRaceStrip.vue` | unclassified | — · 40 elements | 3 aria | `career-deck.html` | — | n-a |
| `career/SkillPlanRow.vue` | unclassified | card · 23 elements | — | `career-deck.html` | card | n-a |
| `career/StatSummary.vue` | unclassified | — · 17 elements | 1 aria | `career-deck.html` | — | n-a |
| `career/TrainingCard.vue` | unclassified | card · 102 elements | — | `career-deck.html` | card | n-a |
| `career/UnityCupEditor.vue` | unclassified | — · 27 elements | — | `career-deck.html` | — | n-a |
| `catalog/FormDetail.vue` | unclassified | card · 12 elements | — | `career-deck.html` | card | n-a |
| `catalog/FormTabs.vue` | unclassified | — · 5 elements | 1 aria | `catalog-detail.html` | — | n-a |
| `catalog/TraineeCatalog.vue` | unclassified | card note · 47 elements | — | `career-deck.html` | card note | n-a |
| `database/ReferenceDrawer.vue` | renamed | card · 6 elements | 1 aria | `career-deck.html` | card | n-a |
| `database/SourceNote.vue` | unclassified | — · 5 elements | — | `career-deck.html` | — | n-a |
| `legacy/AncestryNode.vue` | unclassified | card · 19 elements | — | `career-deck.html` | card | n-a |
| `legacy/SparkChip.vue` | unclassified | pill pill-neutral · 7 elements | — | `career-legacy.html` | pill pill-neutral | n-a |
| `review/CandidateForm.vue` | unclassified | — · 15 elements | — | `career-deck.html` | — | n-a |
| `review/SuggestionHint.vue` | unclassified | — · 2 elements | — | `career-deck.html` | — | n-a |
| `scenario/AlertRow.vue` | unclassified | — · 4 elements | — | `career-deck.html` | — | n-a |
| `scenario/RaceCalendarNote.vue` | unclassified | — · 1 elements | — | `career-deck.html` | — | n-a |
| `scenario/ResourceMeter.vue` | unclassified | — · 6 elements | 1 aria | `career-deck.html` | — | n-a |
| `scenario/ScenarioPanel.vue` | unclassified | — · 44 elements | — | `career-deck.html` | — | n-a |
| `scenario/ScenarioStatusBadge.vue` | unclassified | pill · 3 elements | — | `career-deck.html` | pill | n-a |
| `scenario/TrackblazerFatiguePanel.vue` | renamed | card · 39 elements | 4 aria | `career-deck.html` | card | n-a |
| `scenario/TrackblazerPanel.vue` | near-copy | card · 120 elements | 8 aria | `database-scenarios.html` | card | n-a |
| `scenario/TrackblazerRaceCalendar.vue` | renamed | card · 25 elements | 2 aria | `career-deck.html` | card | n-a |
| `scenario/TrackblazerResourceLedger.vue` | renamed | card · 31 elements | 2 aria | `career-deck.html` | card | n-a |
| `scenario/TrackblazerRoutePlanner.vue` | renamed | card · 58 elements | 4 aria | `career-deck.html` | card | n-a |
| `scenario/UnityCupPanel.vue` | renamed | card · 67 elements | 1 aria | `career-deck.html` | card | n-a |
| `scenario/UnityCupTeamBoard.vue` | renamed | card · 16 elements | 1 aria | `career-deck.html` | card | n-a |
| `scenario/UnityCupTournamentReadiness.vue` | renamed | card · 17 elements | 1 aria | `career-deck.html` | card | n-a |
| `scenario/UraPreFinalReview.vue` | unclassified | — · 32 elements | 1 aria | `career-deck.html` | — | n-a |
| `scenario/WidgetFallback.vue` | unclassified | — · 1 elements | — | `career-deck.html` | — | n-a |
| `skills/SkillCatalog.vue` | unclassified | card note · 34 elements | — | `career-deck.html` | card note | n-a |
| `support/CardPicker.vue` | unclassified | note · 21 elements | — | `career-deck.html` | note | n-a |
| `support/DeckAnalysis.vue` | renamed | card note · 40 elements | 5 aria | `career-deck.html` | card note | n-a |
| `support/SupportCardCatalog.vue` | unclassified | card note · 30 elements | — | `career-deck.html` | card note | n-a |
| `support/SupportSlot.vue` | unclassified | card pill pill-accent pill-neutral · 27 elements | — | `cockpit-unity-cup.html` | card pill pill-accent pill-neutral | n-a |
| `support/SupportTypeMark.vue` | unclassified | — · 13 elements | — | `career-deck.html` | — | n-a |
| `ui/AbsenceNote.vue` | unclassified | — · 1 elements | — | `career-deck.html` | — | n-a |
| `ui/Card.vue` | unclassified | — · 2 elements | — | `career-deck.html` | — | n-a |
| `ui/Checkbox.vue` | unclassified | — · 3 elements | 1 aria | `career-deck.html` | — | n-a |
| `ui/Combobox.vue` | near-copy | card · 8 elements | 2 aria | `career-deck.html` | card | n-a |
| `ui/DataTable.vue` | near-copy | card · 18 elements | 2 aria | `skills-planner.html` | card | n-a |
| `ui/Flash.vue` | unclassified | — · 3 elements | 1 aria | `career-deck.html` | — | n-a |
| `ui/FormField.vue` | unclassified | — · 6 elements | — | `career-deck.html` | — | n-a |
| `ui/PageHeader.vue` | unclassified | — · 7 elements | — | `career-deck.html` | — | n-a |
| `ui/Pagination.vue` | unclassified | — · 7 elements | 1 aria | `career-trainee.html` | — | n-a |
| `ui/Section.vue` | unclassified | — · 4 elements | 3 aria | `database-shop-items.html` | — | n-a |
| `ui/SegmentedControl.vue` | unclassified | — · 7 elements | — | `career-deck.html` | — | n-a |
| `ui/Select.vue` | unclassified | — · 1 elements | 1 aria | `career-deck.html` | — | n-a |
| `ui/StatusBadge.vue` | unclassified | — · 4 elements | — | `career-deck.html` | — | n-a |
| `ui/StickyFooter.vue` | unclassified | — · 7 elements | — | `career-deck.html` | — | n-a |
| `ui/TextInput.vue` | unclassified | — · 1 elements | 1 aria | `career-deck.html` | — | n-a |
| `ui/Textarea.vue` | unclassified | — · 1 elements | 1 aria | `career-deck.html` | — | n-a |

### Counts, and what the 70 unclassified rows mean

| category | rows |
|---|---|
| `near-copy` | 7 |
| `renamed` | 19 |
| `unclassified` — no primitive class (utility-only under CQ-05) | 50 |
| `unclassified` — primitives but no aria token to compare | 20 |
| `app-only — not in the set` | 0 |
| `dead` | 0 |
| **total** | **96** |

`unclassified: 70, reason: the rendered-signature axis cannot separate these rows.` The 50 carry only
token utilities by CQ-05's ruling, so they emit no class family the set also declares; the 20 emit a
primitive family but no accessible name, so "same signature, different name" and "different signature"
are indistinguishable from source. Both groups need a rendered comparison (a browser pass over the app
pages beside the set pages), which this pass did not run.

## Divergences introduced by the fix pass — re-measured 2026-10-23

The 14-file list below is the brief's, and the narrative version is §"Divergences introduced by the fix
pass (14 files)" above; this table is that list re-measured against the post-Phase-3 tree. The set is **static and was drawn from the pre-fix app**, so a signature change can open a divergence
the inherited matrix could not contain. The list is the brief's 14 files. "Still matches" is measured on
the same proxies as the matrix, and every row that could not be measured says so.

| # | file | HFW counterpart (best match) | shared families | still matches | what differs |
|---|---|---|---|---|---|
| 1 | `components/ProvenanceMark.vue` | `career-legacy.html` | `pill pill-neutral card` + 1 aria | yes (proxy) | no measured divergence |
| 2 | `components/SubCell.vue` | `career-deck.html` | `card` | unmeasured | no aria token to compare |
| 3 | `components/ui/DataTable.vue` | `skills-planner.html` | `card` + 1 aria | yes (proxy) | no measured divergence |
| 4 | `components/scenario/ScenarioPanel.vue` | none | none | unmeasured | utility-only (CQ-05); no signature to compare |
| 5 | `components/scenario/TrackblazerRoutePlanner.vue` | `career-deck.html` | `card` | unmeasured | no aria token to compare |
| 6 | `components/scenario/TrackblazerFatiguePanel.vue` | `career-deck.html` | `card` | unmeasured | no aria token to compare |
| 7 | `components/scenario/UraMilestoneTracker.vue` | — | — | **deleted** | removed at `681e8b70`; the divergence is moot, not fixed |
| 8 | `components/career/CareerSectionNav.vue` | `career-deck.html` | `card` + 1 aria | yes (proxy) | the §7.1 note already records the app has a seven-anchor nav none of the four cockpit pages draws — that is `the set is behind`, carried here rather than lost |
| 9 | `pages/Career/Cockpit/Panels/CareerGoalsPanel.vue` | none | none | unmeasured | utility-only (CQ-05) |
| 10 | `pages/Career/Cockpit/Panels/TeamRankLadderPanel.vue` | none | none | unmeasured | utility-only (CQ-05) |
| 11 | `pages/Career/Cockpit/Panels/UraMandatoryLadder.vue` | none | none | unmeasured | utility-only (CQ-05) |
| 12 | `pages/Career/Preflight.vue` | none | none | unmeasured | utility-only (CQ-05) |
| 13 | `pages/Review/Index.vue` | none | none | unmeasured | utility-only (CQ-05); its inline detail is the one sanctioned behaviour change (FQ-10) and has not shipped |
| 14 | `pages/Database/Sources.vue` | none | none | unmeasured | utility-only (CQ-05); the screen is now registered at `SCR-SYS-011` |

**The one divergence routed rather than absorbed.** The set's dark `primary-action` is a literal
`#2255FF` and its dark block never redeclares it, so the set's dark primary fill is its light one, while
the app's `--color-brand-chrome` resolves to `#1A3FCC` in dark (§1.2 above). The port could not close it
and Phase 2 minted no token to paper over it. **Route: D3 registers it as a question with two options** —
(1) correct the set's dark block to the app's `#1A3FCC`, which makes the set an accurate drawing of the
app and invalidates any dark-baseline screenshot taken before the change; (2) accept the divergence and
record it beside the dark baseline, which keeps the frozen set frozen and leaves the set misrepresenting
the app's primary action in dark. The consequence of (1) is a one-line `shell.css` change plus a
re-baseline; the consequence of (2) is that every future dark comparison carries a known false positive.

## Not verifiable — re-derived 2026-10-23

The earlier `## Not verifiable (39 rows)` above routes the *selector* axis; this list routes the
*component* axis and the Phase 3 outcomes, which that list predates. Each item is a question with two options and the consequence of each, ready for D3 to number as
`CQ-nn`.

**N-1. The 70 `unclassified` rows (§7.2).** Option (1): accept the proxy classification and ship the
matrix as a component inventory without parity claims; consequence — the map stops asserting parity it
cannot measure, and the 70 rows stay explicitly open. Option (2): run a rendered comparison (app pages
beside set pages in both themes); consequence — real parity evidence, but it needs a browser pass the
set's checks cannot provide and a stable app build to diff against.

### §7.4 Fresh-build reconciliation (2026-10-23) — the reference count

**Instrument.** Both trees were rebuilt before measuring: `npm run build` at head, and `npm run build`
inside the `b75921b4` worktree. The browser suite serves `public/build`, which is git-ignored, so a run
without a build measures the previous build (see the plan's *Build before measure* convention). This
table is the first measurement in the plan's history where the tested artifact and the tested tree are
the same thing.

**Trees.** Head `59341c94` (+ `ec6c6b65`, the convention commit) on `master`, clean. Base
`b75921b4` in a worktree with `node_modules`, `vendor`, `storage`, `bootstrap/cache` linked and its
own `public/build`.

**Totals.**

| run | tests | passed | failed | spec files |
|---|---|---|---|---|
| head, fresh build, full suite | **544** | **498** | **46** | 25 failing of 77 |
| base `b75921b4`, fresh build, same 25 spec files | 164 | 119 | 45 | 25 |

**Verdicts.** `PRE-EXISTING` **45** · `CAUSED-BY-RECENT-WORK` **1** · `CHANGED` 0.
The single non-pre-existing row is `runs.spec.ts:41` ("renders the run list, and says what happens next
when it is empty"). It **passes standalone at head** (1 passed, 51.5 s) and fails only inside the suite,
so it is order- and timing-dependent rather than caused by a commit: the test is slow, and in suite
order it inherits rows an earlier file created. Recorded as `CHANGED — order-dependent`, not as a
regression.

**Cause tags** (per failing test, from the failure text): `rendering` **40** · `timeout` **5** (an
interaction that never resolved — page-level, cause not yet diagnosed) · `harness` **1**
(`full-career-journey.spec.ts:74` refuses to run without `PLAYWRIGHT_PORT` and `PLAYWRIGHT_SCRATCH_DB`
set; it is a self-declared environment requirement, not a page defect).

**The stale-bundle artifact class, quantified.** 456 tests were measured in both the stale and the fresh
runs. Of those: **22 went fail → pass** and **6 went pass → fail**.
- The 22 are 13 `review` cases (the harness fix, not the bundle) plus **9 genuine stale-bundle
  artifacts**: five `career-cockpit`, two `career-section-nav`, two `containment`.
- The 6 that went the other way are the opposite artifact: **failures the stale bundle was hiding** —
  one `career-deck-select` layout measurement and five `page-header` "`/…` renders one PageHeader h1 at
  the Critical tier" cases. They are genuinely pre-existing (they fail at `b75921b4` too, with a fresh
  build) and only became visible once the bundle matched the tree.

**Per spec.**

| spec | failures | cause | verdict |
|---|---|---|---|
| `ura-panel.spec.ts` | 8 | rendering | PRE-EXISTING |
| `career-legacy-deck-steps.spec.ts` | 6 | rendering | PRE-EXISTING |
| `support-deck.spec.ts` | 5 | rendering + timeout | PRE-EXISTING |
| `unity-cup-panel.spec.ts` | 3 | rendering | PRE-EXISTING |
| `career-build-target.spec.ts` | 2 | rendering | PRE-EXISTING |
| `rice-shower-unity-cup.spec.ts` | 2 | rendering + timeout | PRE-EXISTING |
| `trackblazer-panel.spec.ts` | 2 | rendering | PRE-EXISTING |
| `accessibility.spec.ts` | 1 | rendering | PRE-EXISTING |
| `card-section.spec.ts` | 1 | rendering | PRE-EXISTING |
| `career-deck-select.spec.ts` | 1 | rendering | PRE-EXISTING |
| `career-scenario-select.spec.ts` | 1 | rendering | PRE-EXISTING |
| `career-surface-cutover.spec.ts` | 1 | rendering | PRE-EXISTING |
| `career-trainee-select.spec.ts` | 1 | rendering | PRE-EXISTING |
| `catalog-to-wizard.spec.ts` | 1 | timeout | PRE-EXISTING |
| `choices-so-far.spec.ts` | 1 | timeout | PRE-EXISTING |
| `data-table.spec.ts` | 1 | rendering | PRE-EXISTING |
| `flash-name-first.spec.ts` | 1 | rendering | PRE-EXISTING |
| `flash-name-mechanism.spec.ts` | 1 | rendering | PRE-EXISTING |
| `flash-name-probe-settled.spec.ts` | 1 | rendering | PRE-EXISTING |
| `full-career-journey.spec.ts` | 1 | timeout | PRE-EXISTING |
| `navigation-determinism.spec.ts` | 1 | rendering | PRE-EXISTING |
| `page-header.spec.ts` | 1 | rendering | PRE-EXISTING |
| `runs.spec.ts` | 1 | rendering | CAUSED-BY-RECENT-WORK |
| `scratch-phase1-screenshots.spec.ts` | 1 | timeout | PRE-EXISTING |
| `screen-speed.spec.ts` | 1 | rendering | PRE-EXISTING |
**What 6.1 inherits.** 46 pre-existing-or-order-dependent failures, none caused by recent work. The
release bar ("all failures classify as rendering | harness | stale-bundle and none is
CAUSED-BY-RECENT-WORK") is therefore **met on the counts**, and the debt is named: 40 rendering
assertions across 24 specs, 5 undiagnosed interaction timeouts, 1 environment-gated spec. The largest
blocks — `ura-panel` 8, `career-legacy-deck-steps` 6, `support-deck` 5, `unity-cup-panel` 3 — are the
scenario panels, which no phase of this plan claimed to finish.

**2026-10-25 — Phase 8.1 sweep correction.** The sweep found 13 of 28 Phase 8.1 failures are
stale-spec pins, not component defects: R4-24's removed copy, the per-slot picker migration
(`5f2bfde4`), the `AbsenceValue` floor cells, the closed-dialog controls, and the "Empty." /
"Not equipped" copy. Four are real component defects (BuildTarget summary gate, SupportSlot copy +
Remove control, TrackblazerResourceLedger badge). The "all 46 are real rendering defects" claim is
corrected to "at least 13 of the 28 are stale assertions." See
`docs/research-scratch/AUDIT-AND-VERIFICATION.md`, section `## phase-8.1-browser-triage-2026-10-25.md`.

**Artifacts.** `.scratch-uma/p1/head-fresh.log`, `base-fresh.log`, `head-results-fresh.jsonl`,
`base-results-fresh.jsonl`, `reconciled.jsonl`, `head-errors.jsonl`.

**N-2. `FQ-16`'s Snapshot rows.** Option (1): rows and §4 sections both land (done at `96eca6f6`);
consequence — the registry is internally consistent and §7-25 closes. Option (2): rows only, sections
deferred under a dated §7-25 note; consequence — the registry has rows pointing at no section, which
breaks the file's own convention. Landed as (1).

**N-3. `FQ-17`'s Run detail.** Option (1): Run detail stays out of scope; consequence — the set draws 56
pages and the registry claim says so, and `SCR-RUN-003` is implemented-but-undrawn. Option (2): draw it;
consequence — a 57th page and a re-run of all five set checks. Landed as (1).

**N-4. `SCREEN_SPEC.md` §7-25.** The four Snapshot routes appeared in no §3 row and no §4 section.
Closed at `96eca6f6` by rows + sections. Residual question: whether the assigned ids (`SCR-CAR-025`
through `027`) are the spec owner's numbers. Option (1): keep them, they are the next free in sequence;
consequence — none. Option (2): renumber to the owner's scheme; consequence — one doc edit and a
cross-reference sweep.

**N-5. `SCREEN_SPEC.md` §7-27.** `Database/Sources` shipped without a registry row. Closed at
`96eca6f6` with `SCR-SYS-011`. Same numbering question as N-4.

**N-6. The five test-referenced components.** See the disposition section below. `UraDuelPanel` and
`UraMilestoneTracker` are deleted (not classified). `Checkbox`, `Combobox` and `SegmentedControl` are
live and cited by browser fixtures — they are classified `unclassified` in the matrix for the same reason
as the other 69, and no disposition beyond "kept, cited" was taken.

**N-7. The dark `primary-action` divergence.** Two options in the section above; the outcome changes
whether a committed dark screenshot baseline can exist at all.

## Disposition evidence for the five test-referenced components

| component | state at measure time | evidence | disposition |
|---|---|---|---|
| `scenario/UraDuelPanel.vue` | **deleted** at `681e8b70` | `git grep` over `resources app routes config database lang` returned no production importer before deletion; its guard vocabulary moved to `config/scenarios.php` at `e7621467` | deleted, not classified |
| `scenario/UraMilestoneTracker.vue` | **deleted** at `681e8b70` | same; its `AbsenceChannelTest` entry was removed with it | deleted, not classified |
| `ui/Checkbox.vue` | live | cited by `tests/browser/fixtures/form-field.ts`; emits no primitive family and no aria token → `unclassified` in the matrix | kept |
| `ui/Combobox.vue` | live | cited by `tests/browser/fixtures/combobox.ts` and `combobox.spec.ts`; `:class` bindings, no primitive family → `unclassified` | kept |
| `ui/SegmentedControl.vue` | live | cited by `tests/browser/fixtures/form-field.ts`; `:class` bindings → `unclassified` | kept |

`Checkbox`, `Combobox` and `SegmentedControl` are classified `unclassified` rather than `near-copy`
because none of them emits a primitive family the set also declares, so the signature axis has nothing
to compare. Calling them `near-copy` would be an inherited claim, which is the failure mode §7.1's
disposition rule exists to prevent.

## 8. App-audit record (2026-10-23)

The app-wide frontend audit against `docs/framework-ui-ux/` — 167 files, 73 rules, walk by
walk. This section is the durable record; the working notes (`scope.md`, `ledger.md`,
`followup.md`, `scan.mjs`, log files) were scratch under
`.scratch-uma/app-audit-2026-10-23/` and are not retained.

**Not to be confused with §7.4.** §7.4 counts Playwright test pass/fail rows from the
fresh-build reconciliation; §8 counts checklist-rule compliance across the app's UI files.
Different instruments, different counts, no overlap.

### 8.1 Scope and method

**Files walked — 167.**

| family | count |
|---|---|
| `resources/js/components/**` | 96 SFCs + 5 `.ts` (the `.ts` are `Form/buttonStyles.ts`, `Form/index.ts`, `scenario/registry.ts`, `ui/combobox-types.ts`, `ui/data-table-types.ts`) |
| `resources/js/pages/**` | 58 SFCs (incl. 8 Cockpit panels, 3 Snapshot screens) |
| `resources/js/layouts/**` | 3 (`AppLayout`, `CareerLayout`, `SetupLayout`) |
| `resources/css/app.css` | 1 (1,568 lines at measure time) |
| `resources/views/**` | 4 (Inertia root + 404/419/500) |

**Rules — 73 across 11 framework files** in `docs/framework-ui-ux/`. Classification for this
pass: **45 code-readable / 3 partial / 25 render-required**. The rule list itself is not
reproduced here — the framework files are the authoritative source and are tracked in the
repository.

**Method.** A mechanical scan (`.scratch-uma/app-audit-2026-10-23/scan.mjs`) over every file
for the code-readable patterns — snake_case in visible text, filler copy, `:disabled` without
an adjacent reason, single-character key handlers, `aria-valuenow`/`<progress>`,
`role="status"` presence, error strings, destructive-primary pairings, SVG/filter colour
literals, empty-state copy, input constraints, help links, `lang`/`title` in Blade — followed
by manual deep reads of the highest-risk files: the three layouts, `app.css`, the four Blade
views, `ui/Flash.vue`, `VisitProgressBar.vue`, `Career/Cockpit.vue`, `Career/RacePlanner.vue`
with `RacePlanList.vue`, `SkillPlanRow.vue`, `AcquisitionStatusEditor.vue`, and the provenance
surfaces (`Skills/Show.vue`, `Catalog/Show.vue`).

**In-flight exclusions at pass time:** `pages/Review/Index.vue`, `pages/Review/Detail.vue`
(peer-owned; `Index.vue` was modified by the peer mid-pass, so its ledger row is stale by
design), plus the P5/P6 file set (`VERSION.md`, `CHANGELOG.md`, `docs/proposals/*`,
`.scratch-uma/<session>/`).

### 8.2 Findings summary

Nine findings, two `no-action` items. Every one is a checklist-rule finding; none was edited
during the audit itself (each fix landed in a later pass, below).

| id | rule | file | decision | state |
|---|---|---|---|---|
| D1 | WCAG-4 + H10 | `AppLayout.vue` (whole shell) + `routes/web.php` | deferred | **open** — no help mechanism; gap already registered at `usability-heuristics-2026-10-21.md:233` |
| D2 | IA-2 | `AppLayout.vue:260-300` | deferred | **open** — flat rail of ten destinations; owner ruling already routed by the in-code comment at `AppLayout.vue:140-142` |
| D3 | UI-2 | `app.css` (`--color-stat-*`) | deferred → verified → **fixed** | see §8.3 |
| D4 | UI-2 / UI-1 | `app.css` | deferred | **open** — 230 `literal-colour`/`literal-length` findings outside `@theme static`; tracked by `ui-framework-check` |
| D5 | IA-5 | set-wide empty states (58 files) | deferred | **open** — HFW cross-ref: the checklist's own gap register, HFW audit F7/D4 |
| D6 | UI-1 | `app.css` + shell headings | deferred | **open** — HFW cross-ref: HFW audit F4/D1 interpretation ruling |
| D7 | (25 render-required rules) | app-wide | deferred | see §8.7 |
| D8 | all rules, 2 files | `pages/Review/*` | deferred | **in-flight** — peer-owned; revisit after 4b.2 |
| D9 | (check-script citations) | — | deferred | HFW cross-ref: HFW audit F5 (withdrawn WCAG 4.1.1) and F6 (nonexistent SHR-8) |

| id | rule | file | decision | reason |
|---|---|---|---|---|
| N1 | H2 | `Skills/Show.vue:193`, `Catalog/Show.vue:361` | **no-action** | Provenance footnotes name storage lists (`skills_unique`, …) in muted microcopy. The words are the client's own skill categories, and GP-2 requires a derived figure to name its source — rewording would weaken the source claim. H2 governs the interface's vocabulary, not source citations. |
| N2 | NRM-2 | `SkillPlanRow.vue:121,131`; `AcquisitionStatusEditor.vue:168` | **no-action → reclassified → ruled → fixed** | see §8.4 |

### 8.3 D3 lifecycle — the finding whose shape changed under verification

**Original classification.** Deferred, described as a "dead saturated identity-hue family"
contradicting `DESIGN.md:208`.

**Verification (four greps, all recorded).**

| grep | result |
|---|---|
| declarations in `app.css` | **14 sites** = **7 names × 2 theme blocks** (light `@theme` 168-174, dark override 393-399) |
| `var(--color-stat-` in `app.css` | **0** |
| `color-stat-` in `resources/js` | **0** |
| Tailwind utility spellings (`text/border/…-stat-*`) in `resources/js` | **0** — closes the loophole that a token could be consumed without the literal string `color-stat-` |
| anywhere else (`app/`, `resources/views/`, `config/`, `database/`, `lang/`, `routes/`) | **4 matches, all `tests/Feature/DesignTokensTest.php`** — the family is asserted, not consumed |

**Two corrections to the original framing.**

1. **Not an identity-hue violation.** `app.css:164-167` records the family as "seven stat-mark
   values promoted from `shell.css` §3", and `DesignTokensTest.php:455-457` measures them as
   **non-text boundaries at 3:1** (FQ-05, "computed 4.24–6.23:1"). The role is stat mark /
   boundary, not identity; `DESIGN.md:208`'s tint ruling is not contradicted.
2. **The finding the audit missed.** The `.stat-*` primitives at `app.css:1126-1164` existed
   and hardcoded the identical hexes — `.stat-speed { color:#2F6BC4; }` against
   `--color-stat-speed: #2F6BC4` — under comments claiming to "resolve to house tokens", with
   **no dark override**. So the dark values at `app.css:393-399` were unreachable and the
   primitives painted the light hex in dark mode.

**The correct classification** is therefore *minted, measured, never wired*: token-count
asserted (`DesignTokensTest.php:275-276`), contrast-contracted (FQ-05), and unread by the
layer that should consume it — a Phase 2.2/2.4 wiring gap, not a leftover.

**Fix landed.** `52625c8a` replaced the seven literals with `var(--color-stat-<name>)`
(`.stat-sp` deliberately untouched — it already aliases `--color-sp-ink`).
`ui-framework-check` findings dropped **328 → 314**, i.e. **14 fewer**: seven `[literal-colour]`
and seven `[colour-var]` findings resolved, zero added.

**Rendered delivery pending** the next Playwright window; no spec covers the `.stat-*` classes
today (see §8.7).

### 8.4 N2 lifecycle — rule → classification → challenge → ruling → fix

**Original classification: `no-action`.** The audit reasoned that a top-of-list disable is
self-evident from the visible ordered list and that `aria-label` carries "position N of M" for
assistive technology.

**Challenge.** The audit's own decision table says a rule with two defensible readings is
`deferred`; `no-action` was chosen on the report's authority rather than stated as a ruling.

**Owner ruling: Reading A (strict → edit).** Two reasons: (1) the rule as written requires the
*control's visible presentation* to say why, and `aria-label` reaches assistive technology only;
(2) the codebase already meets the strict reading elsewhere — `RacePlanner.vue:265-267` prints
`{{ chosen.length }} of {{ LIMIT }} chosen` beside the compare controls — so the three sites are
outliers, not an idiom. Reading B was rejected because it would require a named, bounded
exception under `SHR-1` (`shneiderman-golden-rules-2026-10-21.md:135`), which is a document
edit the audit pass could not make.

**Fixes landed.**

| commit | file | change |
|---|---|---|
| `b0cf49e4` | `SkillPlanRow.vue` | container gains `flex-wrap items-center`; a muted span after "Move down" reads `First in the plan` / `Last in the plan`, keyed on `atTop \|\| atBottom` (unconditional on `movable` — position context is useful in a read-only view) |
| `dc00dff9` | `AcquisitionStatusEditor.vue` | muted paragraph after the save button: "Nothing has changed yet, so there is nothing to save.", keyed on `touched.length === 0 && !form.processing` so it does not compete with the button's own "Saving…" label |

No prop/emit/slot/`aria-*` change in either file.

**Spec coverage.** `career-build-target.spec.ts` (accessible-name lookups, focus,
`toBeDisabled`, a 44px height sweep, a 320px document-overflow check) and
`career-skills-planner.spec.ts` (aria-label lookups, focus, `role="status"` announcement, a
44px height sweep, 390/768px overflow checks) — every assertion that touches the move buttons
was read; none asserts row width or `nowrap`, and `flex-wrap` can only reduce horizontal
overflow. Site 3 is covered by `rice-shower-unity-cup.spec.ts:153`
(`planner.saveAcquisitionStatus`).

**Rendered delivery pending** the next Playwright window.

### 8.5 `CHANGELOG ## Deferred` handoff

The 2.1.0 release note should carry these entries. D3's clause reflects the post-`52625c8a`
state.

```markdown
### Deferred — app-shell debt (app-audit D1, D2)

- **D1 — No help mechanism.** The app shell carries no `?` help affordance and
  `routes/web.php` has no help route, so the product has no in-product help surface.
  Checklist rules: WCAG-4 (`wcag-2.2-accessibility-2026-10-21.md:208`, "A help mechanism
  sits in the same relative order on every page") and H10
  (`usability-heuristics-2026-10-21.md:178-180`, help and documentation). The gap is
  already recorded in the checklist's own register (`usability-heuristics:233`). Deferred to
  a post-2.1.0 help-surface addition; needs a route and a page, which is a scope decision.
- **D2 — Flat primary rail.** `resources/js/layouts/AppLayout.vue` renders one unbanded
  `<ul class="space-y-1">` of ten destinations (template at `:260-300`), while IA-2
  (`information-architecture-principles-2026-10-21.md:143`) requires navigation "banded by
  task, never one flat list". The ten-destination count breach is already routed to an owner
  ruling by the in-code comment at `AppLayout.vue:140-142`; `SHR-1`
  (`shneiderman-golden-rules-2026-10-21.md:135`, "A consistency exception must be named in a
  document and be few") requires any accepted exception to be named. Deferred to post-2.1.0
  layout work.

### Deferred — stylesheet debt (app-audit D3, D4)

- **D3 — `--color-stat-*` minted, measured, never wired.** Seven token names declared twice
  (light `@theme` block `app.css:168-174`, dark override `app.css:393-399`) = 14 declaration
  sites. The seven `.stat-*` primitives that should have consumed them hardcoded identical
  hexes until `52625c8a` wired them to `var(--color-stat-*)` — which also made the
  previously-unreachable dark values resolve. The wiring's rendered delivery is unverified
  until the next Playwright window (no spec covers the `.stat-*` classes today). The
  pre-wiring history is in `primitive-reconciliation.md` §8.3; the token was never an
  identity-hue violation of `DESIGN.md:208`, it was a minted-and-measured-but-unwired layer
  (contrast-contracted as non-text boundaries, FQ-05,
  `tests/Feature/DesignTokensTest.php:275-276,455-464`).
- **D4 — Stylesheet literal debt.** `node tools/ui-framework-check.mjs` reports 230
  `literal-colour` / `literal-length` findings in `resources/css/app.css` outside
  `@theme static`. Pre-existing debt, tracked by the check itself; deferred to a stylesheet
  pass.

### Deferred — HFW cross-references

- **IA-5 empty states** — app-side instances of the checklist-registered exemplar gap
  (58 files; `information-architecture-principles-2026-10-21.md:125,146`). Same record as
  the HFW audit's D4/F7; no new entry.
- **UI-1 type scale** — app-side instance of the HFW audit's F4/D1 interpretation ruling.
- **Check-script citation defects** — the HFW audit's F5 (`framework-check.mjs` cites
  withdrawn WCAG 4.1.1) and F6 (cites nonexistent SHR-8) stand as recorded there.
```

**If `CHANGELOG.md` was already cut for 2.1.0, do not retro-edit it** — land a correction
commit or defer these to 2.1.1.

### 8.6 Coverage proof

**167 files walked against the 45 code-readable rules.** Distribution: 101 components
(96 SFC + 5 `.ts`), 58 pages, 3 layouts, 4 views, 1 `app.css`. Every row resolved to either
"clean vs code-readable rules" or "N findings, triaged per scan-rule key". Findings are listed
in §8.2; **no other file raised a finding.**

The per-file clean rows rest on the mechanical scan's zero-hit (or a per-rule triage that
cleared the hit). **19 files were additionally deep-read manually** — the three layouts,
`app.css`, the four Blade views, `ui/Flash.vue`, `VisitProgressBar.vue`, `Career/Cockpit.vue`,
`Career/RacePlanner.vue`, `RacePlanList.vue`, `SkillPlanRow.vue`,
`AcquisitionStatusEditor.vue`, `Skills/Show.vue`, `Catalog/Show.vue`,
`Form/BaseCheckbox.vue`, `ui/Checkbox.vue`, `AppLayout.vue`'s rail/topbar. The audit does not
claim a clean-file *read* where only the scan ran.

**Two cosmetic defects in the working ledger** (recorded, not fixed, since the ledger is
ephemeral): its "Total rows: 166 scanned files + app.css = 166" line has the arithmetic wrong
(166 + 1 = 167) and glued the `app.css` row onto it; and one dispatch check pointed at
`scope.md`, which never carried that line.

### 8.7 Render-required deferrals — the 25 rules

`WCAG-2` (contrast), `WCAG-6` (focus not obscured), `UX-2` (progress distance), `UX-3`
(incomplete vs saved), `UX-6`/`UI-7` (one accent per screen), `UX-8` (Postel on inputs), `UX-9`
(figure framing), `UI-1` (three type sizes), `UI-6`/`GT-1` (space vs border), `IA-3` (multiple
browse axes), `IA-6` (scenario growth by config), `GT-3` (one glyph one meaning), `GT-5`
(decorative motion), `TOG-1` (50ms acknowledgement), `TOG-2` (double-click trap), `TOG-5`
(target count before size), `TOG-6` (undo debt named), `TOG-7` (session resumption), `SHR-1`
(named exceptions); plus the render-confirmation halves of `UX-1` (44px), `UX-7` (actions at
series ends) and `SHR-4` (flow closure).

```bash
npx playwright test                                        # full app suite
node tools/ui-screenshot-diff.mjs --check primitives       # primitive geometry
node docs/design-research/frontend-v2-wireframes/framework-audit.mjs
```

**`.stat-*` rendering coverage — none exists.** `grep -rn "stat-speed|stat-stamina|stat-power|
stat-guts|stat-wit|stat-pal|stat-group|\.stat-" tests/browser/` returns **no matches**. Three
specs mention "five stats" in prose (`career-save-veteran.spec.ts:140`,
`career-training-detail.spec.ts:93`, `grand-concert-panel.spec.ts:57`) but none asserts a
`.stat-*` class, a computed colour, or a screenshot of the stat marks. **The D3 fix is
unverified until visual inspection or a new spec covers it** — and if a dark-theme assertion
ever fails on a stat-mark surface after `52625c8a`, that failure is the fix working (dark hexes
now resolve), not a regression.

### 8.8 Parallel-safety disclosure

- **Peer files at start and end:** `AGENTS.md`, `CLAUDE.md` (both dirty throughout; neither
  read for this record, neither touched).
- **Mtime movement on the audit's targets:** none — every target was re-mtimed immediately
  before its edit and matched the precondition record.
- **Checks not run:** Playwright, Pest, `npm run build`, `framework-audit.mjs` (contended;
  render-required rules deferred per §8.7).
- **Checks run:** `ui-framework-check` four times (328 baseline → 314 after the D3 wiring →
  314 → 314) and `typecheck` four times (exit 0 each). The peer's mid-pass `Review/Index.vue`
  edit moved the check count by zero.

**Durability.** The audit's working tree (`.scratch-uma/app-audit-2026-10-23/`) is gitignored
(`.gitignore:93`), so `git clean -fdx` or a fresh checkout would lose it. This section is the
durable copy, per the owner ruling of 2026-10-23.