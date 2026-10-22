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