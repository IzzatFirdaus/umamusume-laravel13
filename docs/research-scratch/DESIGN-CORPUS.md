# Design System and Research Corpus

## Provenance

1. `DESIGN.md` (1,640 lines)
2. `CONSTRAINTS.md` (973 lines)
3. `FRONTEND-BRIEF-AUDIT.md` (191 lines)
4. `FRONTEND-SPEC-DIVERGENCE.md` (297 lines)
5. `MECHANICS-TRANSLATION-TRIAGE.md` (234 lines)
6. `EXTERNAL-DESIGN-REVIEW-TRIAGE-2026-09-28.md` (131 lines)
7. `SCENARIO-DIFFERENCES.md` (211 lines)
8. `RAW-FINDINGS.md` (367 lines)
9. `SCREENSHOT-MANIFEST.md` (80 lines)
10. `WEB-FINDINGS.md` (390 lines, from `docs/design-research/_scratch/`, appended 2026-10-02)

---

## DESIGN.md

DESIGN.md: Umamusume Trainer Companion design system


Phase 2 deliverable. Design contract only; no production code was written or modified **at the time of writing (2026-09-27)**. Production components now exist: `stat-band`, `resource-strip`, `guided-step`, `race-calendar`, `grade-point-meter`, `design-preview`, and token-migrated `layout` (see root `DESIGN.md` §3). This line is retained for audit traceability.
Anchors in this file were measured from `docs/game-screenshots/` (see `RAW-FINDINGS.md` §3.2). Ramps were derived from those anchors by linear-light mixing. Every text/background pair used for real copy was contrast-checked; the numbers are printed in §3.4.

Read the root `CONSTRAINTS.md` first. This file does not relax it. The design-side contract derived from this system lives in `docs/design-research/CONSTRAINTS.md`.

---

### 1. What this is trying to look like

A piece of the game's own software, re-cut for a keyboard. Not a fan site, not an anime-themed dashboard, not a SaaS tool with a purple gradient and a racetrack photo.

The client's real character, from the corpus: **a high-key near-white interface with warm brown ink, one yellow-green action colour used with restraint, and shape used instead of shadow.** Everything saturated in it is *semantic*: green means "do this / this is affordable", orange means "this went up", blue means "this went down", gold means "this one is selected", crimson means "this is the failure risk".

The three failures this system exists to prevent:

1. **Dark-mode drift.** The instinct that "game UI" means charcoal and neon is wrong for this franchise, so light is the default. Median frame luminance in the corpus is 193/255 (§1.3 RAW-FINDINGS). A dark surface would be unrecognisable.
2. **Palette drift.** Tailwind's default `green-500` is `#22C55E`, a teal-green. The client's action green is `#7FCC09`, a yellow-green at hue ~87°. Six hue degrees of drift and the resemblance is gone. This is the single most consequential token in the file.
3. **Ornament overload.** The client's ornament (lattice, ribbons, sparkles, arrow-caps) works because it sits on a very quiet field. Copy the ornament without the quiet and you get a bootleg. §7 puts a hard budget on it.

#### North star
A Trainer who has spent 400 hours in the client should be able to open this tool, look at the turn timeline, and find the reading effortless, without ever being told what to look at.

---

### 2. Principles

**P1. Light field, saturated meaning.** Surfaces are near-white and quiet. Colour is spent only where it carries information. If a coloured element does not change what the Trainer knows, it should be neutral.

**P2. Preview before commit — informed risk-taking.** The client never lets you fire a decision blind: the Choices panel shows each option's outcome before you accept, and acceptance is a separate action (RAW-FINDINGS §5.1). Every destructive or irreversible-feeling action in this tool gets the same two-step shape. This is also what makes the guided turn flow feel like the game rather than like a form.

The name matters, and it is borrowed: an external mechanics write-up calls this principle **informed risk-taking**, and the phrase is worth adopting because it describes the Trainer's act rather than the UI's (`CONSTRAINTS.md` D-283 governs what else may be taken from that source). A preview whose job is stated as *reducing risk* would soften the number, hide the cost, or recommend a safer option. Its actual job is to make the risk known and leave the decision alone — which is the same boundary P6 draws around predictions. The under-50 Energy caution at the Confirm control (§6.21, D-171) is this principle at the point of commitment rather than a second, kinder preview.

**P3. Numbers are the display voice.** In the client, the biggest and heaviest thing on screen is a number: the turn count, the stat value, the cost. Headlines are modest. Our type scale inverts the web default, where the H1 shouts and the data whispers.

**P4. Shape does the structural work.** Depth and grouping come from stacked silhouettes, ribbons, arrow-caps and notches, not from drop shadows, borders, or blur. See §6.

**P5. Colour never alone.** The client already pairs every state colour with a word (`GREAT`, `Scheduled`, `Hint Lvl 2`). We keep that, which happens to also be the accessibility requirement. A state must be legible to a Trainer who cannot distinguish the hues.

**P6. Deterministic and explainable.** Every number rendered must be traceable to entered turns (`CLAUDE.md` Planner Domain Rules 4 and 5). The UI never shows a figure it cannot point at, no implied recommendations, no simulated forecasts (PRD §6.11).

---

### 3. Colour

#### 3.1 Measured anchors

| Anchor | Hex | Measured from |
|---|---|---|
| Action green, bright | `#7FCC09` | Log header capsule `234521` (1310,40); corroborated `#83CF0A`, `#85D008` |
| Action green, dark (text-bearing) | `#4E7906` | midpoint of the Confirm button gradient `234521`, top `#658A04` / bottom `#376708` |
| Banner green | `#52C518` | discipline banner `194819`; corroborated `#55C618` |
| Info blue | `#0088E0` | cluster peaks `#0084DD` / `#0088E0` / `#007BDE` across three frames |
| Skill cyan | `#009FE1` | cluster peaks `#009FE1` / `#03B3E2` |
| Mood GREAT pink | `#FB5590` | `202142` (560,121) |
| Mood GOOD orange | `#ED8036` | `194819` (560,130) |
| Discount orange | `#FF9A2C` | Hint Lvl badge `234521` (740,417) |
| Selected gold | `#EFC96A` | selected skill row `234521`, body `#ECD38D` with sheen `#FFFCE1` |
| Grade indigo | `#351F70` | cluster peaks `#351F70` / `#311D70` |
| Alert crimson | `#800014` | cluster peaks `#800014` / `#80001A` |
| Ink, body | `#6A5641` | 18.9% coverage of the event-choices frame |
| Ink, strong | `#482720` | 11.8% coverage of the same frame |
| Surface, panel | `#F8F8FB` | dominant-colour pass, 40-60% of UI frames |
| Surface, idle card | `#D2D2DB` | skill row idle fill `234521` |
| Surface, disabled | `#D0D1D0` | unavailable date cell `230755` |

#### 3.2 Derived ramps

Step 500 is the measured anchor, untouched. Tints mix toward white, shades toward a cool near-black `#18121E`, both in linear light so tints stay chromatic instead of going milky. Generation: `_scratch/tokens.py`.

| Token | 50 | 100 | 200 | 300 | 400 | **500** | 600 | 700 | 800 | 900 |
|---|---|---|---|---|---|---|---|---|---|---|
| `green` | F9FCF7 | F0F8EC | E0F0D7 | C6E5B3 | A4D77C | **7FCC09** | 74BB0E | 68A713 | 598F17 | 49741A |
| `green-action` | F8F9F7 | EEF0EC | DADFD7 | BAC4B3 | 8CA07C | **4E7906** | 486F0C | 406312 | 385416 | 2F4419 |
| `blue` | F7F9FD | ECF1FA | D7E2F6 | B3C9EF | 7CA9E7 | **0088E0** | 057CCD | 0B6FB8 | 105F9E | 134D81 |
| `cyan` | F7FAFD | ECF3FB | D7E6F6 | B3D1EF | 7CB8E7 | **009FE1** | 0592CE | 0B82B9 | 106F9F | 135A81 |
| `pink` | FFF8F9 | FEEEF2 | FEDBE3 | FDBCCC | FC8FAE | **FB5590** | E64E84 | CF4577 | B13B66 | 8F3053 |
| `amber` | FEF9F7 | FCF0ED | F9E0D9 | F5C7B6 | F1A484 | **ED8036** | D97533 | C3692F | A7592B | 874826 |
| `orange` | FFFAF7 | FFF2ED | FFE5D8 | FFD0B5 | FFB481 | **FF9A2C** | EA8D2A | D27E28 | B46C25 | 925723 |
| `gold` | FEFCF8 | FDF7EF | FAEFDD | F6E3C0 | F2D598 | **EFC96A** | DBB861 | C5A558 | A88D4C | 89723F |
| `indigo` | F7F7F8 | EDECEF | D9D8DE | B6B4C2 | 837F9B | **351F70** | 311D67 | 2D1B5D | 281950 | 231642 |
| `crimson` | F9F7F7 | F0ECEC | E0D7D7 | C7B3B4 | A47C7D | **800014** | 750416 | 690718 | 5A0B1A | 490E1C |
| `ink` | F8F8F7 | EFEEED | DDDBD9 | C0BCB8 | 988F87 | **6A5641** | 614F3C | 574637 | 4B3C31 | 3D312B |

The `ink` ramp doubles as the warm neutral ramp for hairlines and disabled text. `ink-strong` `#482720` is a separate token, not a step of this ramp: it is the client's heading ink and it is browner and darker than `ink-700`.

#### 3.3 Semantic roles

This is the part that makes it the game's UI rather than a palette. The mapping follows the client's own usage (RAW-FINDINGS §3.2, §5).

| Role | Token | Rule |
|---|---|---|
| Page field | `#F2F1F8` | the pale lavender behind panels; carries the faceted texture (§7) |
| Panel surface | `surface-panel` `#F8F8FB` | default card and modal body |
| Raised surface | `#FFFFFF` | a card sitting on a card |
| Ink, running text | `ink-body` `#6A5641` | never `gray-900`, never black |
| Ink, headings and values | `ink-strong` `#482720` | the big numerals |
| Ink, secondary labels | `#7A7067` | the lightest ink permitted for small text (§3.4) |
| Affirmative action | `green-action-500` `#4E7906` | the only fill allowed to carry white button text |
| Action decoration, borders, focus | `green-500` `#7FCC09` | bright green: rings, capsule fills behind dark ink, lattice. Never behind white text. |
| Increase / gain | `orange-500` `#FF9A2C` | **not green.** The client renders "up" in orange. |
| Decrease / loss | `blue-500` `#0088E0` | **not red.** The client renders "down" in blue. |
| Skill Point identity | `cyan-500` `#009FE1` | the Skill Pts column and SP cost |
| Selected | `gold-500` `#EFC96A` | fill, with `ink-strong` on top (8.34:1) |
| Rank / grade badge | `indigo-500` `#351F70` | and the badge letter scale (§6.7) |
| Failure risk, validation error | `crimson-500` `#800014` | reserved: the client uses red only for training failure |
| Turn / phase structure | `blue-500` | the turn card and phase bars |
| Disabled | `surface-disabled` `#D0D1D0` + `ink-300` | |

**The inverted delta rule is the most copyable-to-wrong thing in this system.** A developer reaching for `text-green-600` on a stat gain would silently break the resemblance. It is called out as a hard rule in `CONSTRAINTS.md`.

#### 3.4 The contrast contract

The client's own saturated chrome is decorative and repeatedly fails WCAG. Measured, white text on:

| Surface | Ratio | Verdict |
|---|---|---|
| `green-500` `#7FCC09` | 1.99 | fails |
| banner green `#52C518` | 2.24 | fails |
| `cyan-500` `#009FE1` | 2.98 | fails |
| `amber-500` `#ED8036` | 2.71 | fails |
| `orange-500` `#FF9A2C` | 2.12 | fails |
| `pink-500` `#FB5590` | 3.09 | large text only |
| `blue-500` `#0088E0` | 3.75 | large text only |

We do not ship those. The client itself already solves it once: its Confirm button is not bright green, it is a dark olive gradient (`#658A04` to `#376708`) carrying white at 5.17:1. The system therefore splits every hue in two:

- **Chrome step (500):** fills, borders, rings, lattice, and anything non-text. Keeps the measured, recognisable colour.
- **Ink-bearing step (600-800):** any surface with text on it.

Approved text pairs, all computed:

| Foreground | Background | Ratio | Level |
|---|---|---|---|
| `ink-strong` `#482720` | `surface-panel` | 12.49 | AAA |
| `ink-body` `#6A5641` | `surface-panel` | 6.56 | AA |
| `ink-body` | `#FFFFFF` | 6.95 | AA |
| `ink-body` | `surface-idle` `#D2D2DB` | 4.63 | AA |
| `ink-body` | `surface-disabled` `#D0D1D0` | 4.54 | AA |
| `#7A7067` (secondary label) | `surface-panel` | 4.56 | AA |
| `#FFFFFF` | `green-action-500` `#4E7906` | 5.17 | AA |
| `#FFFFFF` | `green-action-900` `#2F4419` | 10.70 | AAA |
| `#FFFFFF` | `cyan-800` `#106F9F` | 5.53 | AA |
| `#FFFFFF` | `blue-700` `#0B6FB8` | 5.28 | AA |
| `#FFFFFF` | `crimson-500` `#800014` | 10.89 | AAA |
| `#FFFFFF` | `indigo-500` `#351F70` | 13.28 | AAA |
| `#FFFFFF` | `amber-900` `#874826` | 7.03 | AAA |
| `#FFFFFF` | `orange-900` `#925723` | 5.82 | AA |
| `ink-strong` | `gold-500` `#EFC96A` | 8.34 | AAA |
| `ink-body` | `gold-200` `#FAEFDD` | 6.11 | AA |
| `ink-body` | `amber-200` `#F9E0D9` | 5.53 | AA |
| `ink-body` | `pink-200` `#FEDBE3` | 5.45 | AA |
| `ink-body` | `orange-100` `#FFF2ED` | 6.35 | AA |
| `ink-body` | `blue-100` `#ECF1FA` | 6.13 | AA |
| `on-mood` `#1F1508` | `mood-great` `#FB5590` | 5.82 | AA |
| `on-mood` `#1F1508` | `mood-good` `#ED8036` | 6.62 | AA |
| `on-mood` `#1F1508` | `mood-normal` `#A0978E` | 6.25 | AA |
| `on-mood` `#1F1508` | `mood-bad` `#D48556` | 6.23 | AA |
| `on-mood` `#1F1508` | `mood-awful` `#D47E9E` | 6.23 | AA |
| `on-green` `#1F1508` | `green-500` `#7FCC09` | 9.02 | AAA |

The last six rows are measured from the rendered element in both themes on 2026-09-28
(`slice-6-2026-09-28.md` §2 and §3), not computed from the table above, and they read the same in
both themes because the five mood fills and `green-500` are chrome that does not move between
themes: the client paints the same pink whatever surface it sits on, and `--color-green` is the
same #7FCC09 in the dark block. Their ink rows are therefore single-valued.

**`text-risk` on `bg-raised` (added 2026-09-29, catalog roster tree).** The catalog's
unconfirmed-card badge puts `--color-risk` on a card surface. KI-20 measured that pair for the shop
panel and left no row here, so this is the pair's first entry in the contract. `resources/css/app.css`
declares light `--color-risk: #800014` (`:78`) on `--color-raised: #FFFFFF` (`:41`), and dark
`--color-risk: #FF7E8C` (`:243`) on `--color-raised: #24262A` (`:227`). Those two are the only
`bg-raised` declarations in the theme, so they are the only grounds the badge can land on.

Computed with the WCAG 2.1 relative-luminance formula, linearising each sRGB channel as
`c <= 0.03928 ? c / 12.92 : ((c + 0.055) / 1.055) ^ 2.4` on the 0-1 component, then
`L = 0.2126 R + 0.7152 G + 0.0722 B`, then `ratio = (L_lighter + 0.05) / (L_darker + 0.05)`:

| Foreground | Background | Ratio | Level |
|---|---|---|---|
| `text-risk` `#800014` (light) | `bg-raised` `#FFFFFF` | 10.89 | AAA |
| `text-risk` `#FF7E8C` (dark) | `bg-raised` `#24262A` | 6.22 | AA |

Both clear AA for body text, so no token moves and no new colour role is introduced. The same
calculator reproduces rows already in this section from their own named hex values, which is
what makes these two worth trusting: the `ink-body` `#6A5641` on `#FFFFFF` pair = 6.95,
`#ECEAF2` on `#24262A` = 12.71 (§3.7), and the white-on-`crimson-500` row above, which the
formula also puts at 10.89. Measured on 2026-09-29 at tree `0fbff04`.

The four ratios KI-20 records in `KNOWN-ISSUES.md` (the `KI-20` heading — cited by heading, not by line,
because line anchors in that register move under it) do not reproduce from the hex values they
name, and all four are understated in the same direction: `#800014` on `#FFFFFF` computes to 10.89
where 10.04 is recorded, `#FF6B7A` on `#24262A` to 5.51 where 4.33 is, `#FF7E8C` on the same ground to
6.22 where 4.77 is, and its `bg-risk` cross-pair `#121013` on `#FF7E8C` to 7.77 where 6.15 is. No
variant tried here (skipping the linearisation, averaging the three channels) yields the recorded set
either, so those figures were not produced from those inputs by the formula this section states. The
consequence is narrow and it is not a theme defect: under the corrected arithmetic the before-step
value already cleared 4.5:1, so the `#FF6B7A` to `#FF7E8C` step was not required for this pair —
though it moves in the improving direction, and D-259 may have had grounds beyond the ratios quoted
in the register. Every combination passes either way, so no token moves here. KI-20's figures are its own
closed-issue record and are left as written; only the two rows above are added.

**State pills use the tint-and-border pattern** for chips on a surface: a pale fill, a 2px border
in the hue, `ink-strong` text. **Mood is the exception, ruled 2026-09-28.** The mood chip keeps the
client's flat saturated pill and steps the ink instead of the fill, because three of the five tiers
were derived to their neighbours' luminance (§6.17 item 1) and a pale tint of a hue that already
cannot be told from its neighbour turns a status chip into a wash. Measured against this row's own
ink, `ink-strong` #482720 on `mood-great` is 4.29:1, which fails; `on-mood` #1F1508 on the same
fill is 5.82:1, which passes, and it is the darkest member of the ink family the system already
uses. The pill never shrinks to buy the contrast: D-259 makes the arrow the part that has to stay
readable, so a smaller pill is the wrong fix for a contrast failure.

**R74 — the quiet-edge exception (recorded 2026-09-29, docs only; no token and no component changed).**
A non-text boundary is allowed to sit under 3:1 against its own background where the state it marks is
carried by other means, and this system has now done that twice on purpose.

| Case | Quiet edge | Measured | What carries the state instead |
|---|---|---|---|
| Slice 2 lock cells | `border-rule` on a `sunken` fill | under 3:1 | the lock's own glyph and the fan figure in the cell text |
| Slice 13 disclosure toggle, unselected | `border-rule` on the form's `raised` ground | **1.22 light / 1.37 dark** | the label's text contrast (12.49 selected vs 5.46 unselected light; 18.93 vs 8.30 dark), the `pick-line` border on the selected member at **4.83** against the quiet one, and `aria-pressed` |

Both measurements are resolved-property reads from the rendered element, `getComputedStyle` against the
first fully opaque ancestor, in a live server (`slice-13-2026-09-29.md` §5.2, `slice-14-2026-09-29.md`).

The exception is narrow and it is not a licence. WCAG 1.4.11 asks for 3:1 on the visual information
used to identify a component or its state; where a boundary is the *only* signal, it still fails and
must be stepped. What this note permits is a deliberately quiet **inactive** state next to a loud
active one, on three conditions: the selected member clears 3:1 on its own edge, the two are at least
3:1 apart from each other, and the state is announced programmatically rather than drawn. A pair that
loses any of the three has lost the exception with it.

It is written here rather than only in a verification record because it is a rule about future
tokens: the next quiet border someone is tempted to ship should meet this row and the two precedents,
not rediscover them. The judgement about whether the treatment is the one the design system wants
still belongs to the token owner, and R74 records the exception, not a blessing of any specific value.

#### 3.5 Tailwind v4 theme block

Tailwind v4 is CSS-first in this repo: the theme lives in an `@theme` block in `resources/css/app.css`, and there is deliberately no `tailwind.config.js` (`ARCHITECTURE-ESSENTIALS.md`). The block below is the contract, written for reference. **It is not applied to the app in this session** (scope limit; PRD §6 and the root `CONSTRAINTS.md` own any change to `resources/`).

```css
@theme {
  /* surfaces */
  --color-page: #F2F1F8;
  --color-panel: #F8F8FB;
  --color-raised: #FFFFFF;
  --color-idle: #D2D2DB;
  --color-sunken: #E7E7EC;
  --color-disabled: #D0D1D0;

  /* ink */
  --color-ink: #6A5641;
  --color-ink-strong: #482720;
  --color-ink-muted: #7A7067;
  --color-ink-faint: #988F87;   /* large text and disabled only */
  --color-rule: #E4E1EA;

  /* action green: bright is chrome, deep is ink-bearing */
  --color-green: #7FCC09;
  --color-green-deep: #4E7906;

  /* chrome pair: the fill for capsules and primary buttons, plus the ink that sits on it.
     These two are the ONLY tokens that flip hue-family between themes, and they exist so
     that no component ever needs to know which theme is active. See 3.7 and CONSTRAINTS D-101. */
  --color-chrome: #4E7906;
  --color-on-chrome: #FFFFFF;
  --color-green-tint: #F0F8EC;
  --color-green-line: #C6E5B3;

  /* semantic */
  --color-up: #FF9A2C;          /* stat increased */
  --color-down: #0088E0;        /* stat decreased */
  --color-sp: #009FE1;          /* Skill Points */
  --color-pick: #EFC96A;        /* selected */
  --color-risk: #800014;        /* training failure, validation error */
  --color-rank: #351F70;        /* grade and rank badges */

  /* mood scale, five states. The five tier WORDS are now measured: the client's own
     Mood Effect panel prints GREAT / GOOD / NORMAL / BAD / AWFUL (owner-supplied
     primary evidence, 2026-09-27; see §6.17). The token names follow those words, so
     the former mood-peak / mood-poor / mood-worst are renamed.
     Colours are a different matter. Only GREAT and GOOD are measured point probes
     (§3.1). The Mood Effect capture gives the words and the numbers but no usable point
     probe for NORMAL, BAD or AWFUL: it is a legend panel, and sampling its row bands
     averages the pill against the panel field, which is why the same method returns a
     dark crimson where §3.1's probe on the HUD pill measures #FB5590. So those three are
     DERIVED, not measured: each keeps the hue and chroma this section previously decided
     and is solved by bisection to the mean relative luminance of the two measured anchors
     (0.3136), the same method §3.6 uses for the stat tints. Consequence recorded in
     D-259: at equal luminance GOOD/BAD sit 3.3 deg apart and GREAT/AWFUL 1.0 deg, so the
     hue carries no ordinal information and the arrow plus the word carry all of it.
     PROVISIONAL until the client's own three lower pill colours are captured. */
  --color-mood-great: #FB5590;    /* measured, §3.1 */
  --color-mood-good: #ED8036;     /* measured, §3.1 */
  --color-mood-normal: #A0978E;   /* derived at anchor luminance, provisional */
  --color-mood-bad: #D48556;      /* derived at anchor luminance, provisional */
  --color-mood-awful: #D47E9E;    /* derived at anchor luminance, provisional */
  --color-on-mood: #1F1508;       /* the ink all five take; §3.4's measured rows */

  /* geometry */
  --radius-panel: 14px;
  --radius-card: 10px;
  --radius-chip: 8px;
  --radius-badge: 5px;
  --shadow-lift: 0 2px 0 rgba(53,31,112,.06), 0 6px 16px -6px rgba(53,31,112,.22);
  --shadow-press: 0 1px 0 rgba(53,31,112,.10), 0 2px 6px -2px rgba(53,31,112,.18);
  --shadow-glow: 0 0 0 3px rgba(127,204,9,.35);

  /* type */
  --font-display: "M PLUS Rounded 1s", "Hiragino Maru Gothic ProN", ui-rounded, system-ui, sans-serif;
  --font-body: "M PLUS Rounded 1s", "Hiragino Maru Gothic ProN", ui-rounded, system-ui, sans-serif;
  --font-numeral: "M PLUS Rounded 1s", ui-rounded, system-ui, sans-serif;
}
```

The `--font-*` values above are a **proposal pending the web study** (RAW-FINDINGS §6) and remain open in §11. No font is added to the project without the dependency approval the root `CONSTRAINTS.md` C-8 requires.

#### 3.6 Stats are not colour-coded, and why that is the right call

The client does **not** give the five stats identity colours. Its stat band header is a uniform pale slate, and only Skill Pts is separated (by a measured teal). The five identities live in the 3D icons: shoe, heart, dumbbell, horn, book (`RAW-FINDINGS.md` §3.6).

An earlier draft of this system assigned each stat a hue. That was wrong, and the reason is worth keeping because it explains the whole palette:

1. **Every hue in this system is already semantic.** Green is action, orange is increase, blue is decrease, cyan is Skill Points, gold is selected, crimson is risk, indigo is rank. A fifth of the palette spent on "this column is Power" leaves nothing for meaning.
2. **It collides.** The draft gave Guts `#800014`, which is the same hex as the failure-risk colour, and Wit `#4E7906`, the same as the action button. A Guts delta tinted like a validation error is a defect waiting to ship.
3. **It is the rainbow-coding tell** that generic dashboards fall into, and it is unjustified here because the source material does not do it.

**Resolution:** the five stats carry **no saturated identity hue**. What they do carry, and what an earlier revision of this section ruled out too fast, is a **subtle band tint** at the head of each column: a whisper of warmth or coolness, not a colour chip.

The distinction is the whole point. A saturated hue per stat collides with the semantic roles in §3.3 and is the rainbow-coding tell. A tint at 5% chroma and 93.5% lightness is 1.04 to 1.14 contrast against the panel surface, which is below the threshold where it reads as "colour" and above the threshold where it reads as "these columns are different." It does the wayfinding job without spending a semantic hue.

| Stat | Hue | Band tint | Band rule | Dark tint | Ink contrast |
|---|---|---|---|---|---|
| Speed | 220 | `#E6ECF7` | `#CCD6EA` | `#252C42` | 5.86 |
| Stamina | 320 | `#F7E6F1` | `#EACCE0` | `#3B2736` | 5.81 |
| Power | 38 | `#F7F1E6` | `#EADFCC` | `#312C2A` | 6.18 |
| Guts | 358 | `#F7E6E6` | `#EACCCD` | `#3F272B` | 5.77 |
| Wit | 100 | `#ECF7E6` | `#D6EACC` | `#26302A` | 6.30 |
| Skill Points | 192 | `#E6F4F7` | `#CCE4EA` | `#242E35` | 6.17 |

All six clear AA in light with `ink` on top.

**The dark column was wrong in an earlier revision and the fix is the lesson.** It was derived at equal HSL lightness, which is not the same as equal relative luminance: yellow and green carry far more luminance than blue at the same L. Measured against the raised cell the first attempt spread 1.45 to 1.91, so the row read as six different strengths instead of one family. Each dark tint is now solved by bisection on a per-hue mix fraction until it lands at 1.11 contrast against `#24262A`, which yields a tight 1.09 to 1.11. The fractions themselves vary by 3x between hues and that is correct; only the luminance step is meant to be constant. The tints sit at equal lightness (luminance spread 1.097:1) so the row reads as one family rather than six accidents. Two pairs are close in hue, Speed/Skill Points at 28 degrees and Power/Guts at 40, and that is accepted because the tint is never the only signal: the label and the glyph are always present (P5, D-12), and Skill Points is additionally distinguished by having no grade badge at all.

The glyph plus label remains the primary identifier. Where a chart genuinely needs five series, the series are drawn from the existing ramp steps in a fixed published order, each is labelled directly at the line end rather than in a colour-only legend, and adjacent series must clear 3:1 against each other. If a chart cannot meet that, it becomes a table.

Skill Pts keeps its cyan because in the client it is a genuinely separate column with its own header colour, not a sixth stat.

#### 3.7 Dark theme

Light is the default and stays the default: the client is a high-key interface (§1, failure 1), and the faceted near-white field is what makes the tool read as this franchise at first glance.

Dark is a supported theme, not a prohibited one. An earlier revision of this file put dark mode out of scope on the grounds that it would be "a second design system, not a variable swap." That argument was wrong in its own terms: the client-resemblance case only argues against dark being the *default*, and the owner has asked for it. The real cost is that a dark theme needs its own measured anchors rather than an inversion. It has them.

**The measured dark reference is the raceboard**, `Screenshot 2026-07-18 000919.png`, the one genuinely dark surface in the client (frame luminance 97 against a corpus median of 193). It is not a generic dark-mode skin. It is a **stadium display board**: warm amber dot-matrix numerals on a charcoal field, blue position discs, a red LED state block. That gives the dark theme a character within the franchise instead of a default.

| Token | Hex | Measured from |
|---|---|---|
| Base surface | `#121013` | board charcoal, `000919` at (1650,620) |
| Raised surface | `#24262A` | unlit LED numeral cell, at (1240,700) |
| Frame | `#171519` | board edge, at (1770,300) |
| Ink, primary | `#ECEAF2` | derived warm-cool white; the board's own lit text peaks at `#AEAFBC` |
| Ink, secondary | `#AAABB5` | "Kyoto" label on the board |
| Display amber | `#F5B73C` | peak of the DST dot-matrix numerals |
| Display amber, deep | `#BE8535` | mid-tone across a lit numeral |
| Position blue | `#0170D7` | Roman numeral disc peak |
| LED red | `#FF1618` | FINAL state block peak |

Dark mode is a **token override**, not a second system. The semantic roles in §3.3 do not change. Only the surfaces, the ink, and which step of each hue is used flip.

One extra pair makes that possible without branching components. `--color-chrome` is the fill used by capsule headers and primary buttons, and `--color-on-chrome` is the ink placed on it. Light mode sets them to deep green plus white; dark mode sets them to the bright brand lime plus near-black. Every component rule reads the pair and never asks which theme is active, which is what `CONSTRAINTS.md` D-101 requires.

```css
/* Dark theme: applied as html[data-theme="dark"]. Same token names, so every
   component rule in section 6 continues to work untouched. */
:root[data-theme="dark"] {
  --color-page: #0D0C0F;
  --color-panel: #121013;      /* measured: board charcoal */
  --color-raised: #24262A;     /* measured: unlit LED cell */
  --color-idle: #32343A;
  --color-sunken: #1A181D;
  --color-disabled: #3A3A40;
  --color-ink: #ECEAF2;
  --color-ink-strong: #FFFFFF;
  --color-ink-muted: #AAABB5;  /* measured: board label text */
  --color-ink-faint: #7C7D87;
  --color-rule: #2E2C33;
  --color-green: #7FCC09;
  --color-green-deep: #9BE83A;
  --color-chrome: #7FCC09;      /* bright lime is accessible as a fill here: 9.51:1 */
  --color-on-chrome: #121013;
  --color-green-tint: #1F2A12;
  --color-green-line: #3D5A18;
  --color-up: #FF9A2C;
  --color-down: #4EA1E8;
  --color-sp: #4FC3F7;
  --color-pick: #F5B73C;       /* measured: display amber */
  --color-risk: #FF6B7A;
  --color-rank: #B6B4C2;
  --shadow-lift: 0 2px 0 rgba(0,0,0,.4), 0 6px 18px -6px rgba(0,0,0,.7);
  --shadow-glow: 0 0 0 3px rgba(155,232,58,.32);
}
```

**Dark mode solves the problem light mode could not.** The brand green `#7FCC09` fails as a text background in light mode at 1.99:1, which forced capsule headers onto the deeper `#4E7906` (§6.3). On the dark base it passes as a *fill with near-black ink* at 9.51:1, and as a *foreground* at 9.51:1. So the dark theme renders the client's actual bright lime chrome more faithfully than the light theme does, while clearing AAA. That is a real design argument for the theme, not a concession.

| Pair | Ratio | Level |
|---|---|---|
| `#ECEAF2` ink on `#121013` panel | 15.88 | AAA |
| `#ECEAF2` ink on `#24262A` raised | 12.71 | AAA |
| `#121013` ink on `#7FCC09` capsule | 9.51 | AAA |
| `#121013` ink on `#9BE83A` button | 12.62 | AAA |
| `#F5B73C` amber on `#121013` | 11.93 | AAA |
| `#AAABB5` muted ink on `#121013` | 8.30 | AAA |
| `#4EA1E8` down-blue on `#121013` | 6.84 | AA |
| `#FF6B7A` risk on `#121013` | 6.88 | AA |
| `#4FC3F7` Skill Points on `#121013` | 9.45 | AAA |

Three consequences a reviewer should check, because they are where a naive inversion breaks:

1. **The faceted page field inverts to a barely-there lighter facet**, not a darker one. Texture on a dark ground must lift or it disappears.
2. **Shadows stop doing the work.** On `#121013` a shadow has nowhere to go, so separation comes from the `--color-rule` edge and the raised/sunken step. Do not keep the light theme's violet-cast shadow and expect depth.
3. **Brown ink does not survive.** Warm brown on charcoal reads as mud, which is why the dark ink moves to a near-white with only a trace of warmth. This is the one place the two themes genuinely diverge in character, and it is deliberate.

Selection stays amber in both themes, so the gold-on-light and amber-on-dark states remain the same idea. Mood pills are the exception to the tint-and-border treatment (§3.4) and are the client's saturated fills in both themes, with `--color-on-mood` as their ink; because neither the fills nor the ink is theme-relative, the five mood pairs read identically in light and dark and are declared once.



#### 3.8 Measured, derived, or decided

Being explicit about which is which, because it affects how much weight the reader should give each line.

- **Measured:** all of §3.1, plus `mood-great` and `mood-good`, and the five dark surfaces in §3.7. The five mood tier **words** are measured too (§6.17); only their three lower pill **colours** are not.
- **Derived:** all ramp steps other than 500, by the mixing rule in §3.2. `mood-normal`, `mood-bad` and `mood-awful` are derived by a second rule, bisection to the mean luminance of the two measured mood anchors, and are provisional (D-259).
- **Decided:** `ink-muted` `#7A7067` (chosen as the lightest warm neutral that still clears 4.5:1 on the panel, so it is not a ramp step), the hue and chroma of the three provisional mood steps (neutral is the safe default for the middle state; the two low states were placed inside measured families rather than given a new hue, per D-26), the page field `#F2F1F8`, and the dark theme's `--color-page`, `--color-idle`, `--color-rule` and ink steps, which are derived from the measured board charcoal rather than measured directly.


---

### 4. Typography

#### 4.1 Families

**Mandate: a rounded humanist sans. Geometric and neutral grotesques are banned as the primary voice.** This is a hard rule, not a preference.

The client's letterforms are rounded: open terminals, double-storey `a`, wide counters, soft corners on every stem junction (RAW-FINDINGS §3.3). A neutral grotesque is measurably wrong here, and it is also the single most reliable tell of a generated interface.

Banned as body or label faces: **Inter, Roboto, Helvetica, Arial, system-ui, `-apple-system`, Geist, Space Grotesk**, and any stack that resolves to one of them for the running text. `system-ui` is banned specifically because it silently becomes the platform default grotesque, so a build can pass review on one machine and read as a generic web app on another.

**Weight coverage is the deciding constraint, not taste.** The scale in §4.2 needs 500, 600, 700 and 800. Coverage was queried from the Google Fonts CSS API on 2026-09-27:

| Candidate | Weights served | Verdict |
|---|---|---|
| **Nunito** | 200-900 | **Recommended.** Full coverage, rounded humanist, tabular figures, and its numerals stay open at 800. |
| M PLUS Rounded 1s | 100-900 static | Best on one axis: it is the only candidate with Japanese coverage in the same family, so `name_ja` (US-1) stops needing a fallback. Its Latin is slightly narrower than the client's. |
| Quicksand | 300-700 | **Fails the scale.** No 800, and `numeral-xl`/`numeral-lg` are 800. Its numerals are also geometric and light, which weakens the one thing the type must do loudest. |
| Varela Round | **400 only** | **Cannot be used.** One weight means the entire hierarchy in §4.2 collapses to synthetic bolding, which browsers render as smeared faux-weight at 700 and 800. |
| ui-rounded / system | varies | Not a design choice. Present only as a degradation target, never as the declared stack. |

Recommended stack, in order, with the reasoning kept visible:

```css
--font-body: "Nunito", "M PLUS Rounded 1s", ui-rounded, "Hiragino Maru Gothic ProN", sans-serif;
```

`M PLUS Rounded 1s` sits second so that Japanese glyphs fall through to a face that has them rather than to a platform default, which is what keeps `name` and `name_ja` on the same baseline and x-height. Latin metrics must not shift when a Japanese string is present.

**One honest caveat, because the web study contradicts the client.** Cygames' own *web* properties do not use a rounded face. `umamusume.com` loads only variable Roboto and builds its personality from `700` italic plus `0.05em` tracking; `umamusume.jp` uses `YakuHanJP, Roboto, "Zen Kaku Gothic New"` at 15px/500, and its private display family `midasi-w` is literally Roboto files under a different name. So the web is a Roboto site and the client is a rounded-font game.

This system follows the **client**, deliberately. The tool's job is to feel like the software a Trainer already spends hours in, not like the marketing site. That makes the rounded mandate a decision with a known counter-example rather than an assumption, and it is worth revisiting only if the owner would rather the tool align to the web identity.

The italic-700-with-tracking device, however, transfers well and is adopted for the celebratory register in §4.3.

#### 4.2 Scale

The scale is built so that **data outranks chrome**, which is the client's actual hierarchy.

| Role | Size / line | Weight | Used for |
|---|---|---|---|
| `numeral-xl` | 40 / 44 | 800 | stat values, turn count on the dashboard |
| `numeral-lg` | 28 / 32 | 800 | stat values in the band, SP total |
| `numeral-md` | 20 / 24 | 700 | costs, deltas, per-turn values |
| `title` | 18 / 24 | 700 | panel capsule header, modal header, run name |
| `label-strong` | 15 / 20 | 700 | card titles, skill names, stat labels |
| `body` | 14 / 21 | 500 | running text, descriptions, log prose |
| `label` | 13 / 18 | 600 | field labels, tab labels, column heads |
| `meta` | 12 / 16 | 500 | caps, timestamps, provenance lines |
| `micro` | 11 / 14 | 600 | badge text, grade letters |

Rules:
- `title` is the largest *text* role. Nothing in a data view is set larger than a numeral except a panel title. This is the deliberate inversion of web convention (P3).
- Letter-spacing: `0` for body, `+0.01em` for `label` and `micro` (small caps-adjacent text needs the air), `-0.02em` for `numeral-xl` so big digits do not gape.
- No `text-transform: uppercase` on sentences. The client capitalises words (`GREAT`, `Scheduled`), not paragraphs.
- Numerals use `font-variant-numeric: tabular-nums` everywhere they align in a column. The turn table is unreadable with proportional figures.
- Line height on `body` is 1.5. The client's log prose is generous, and our log carries the same load.

#### 4.3 The display register

The client reserves one loud typographic voice for celebration: italic, heavy, gradient-filled with an outline ("Rank 19", "RACE FINISHED"). We have exactly one legitimate use for it: **a run reaching `Retired`** (the `RunStatus` enum). It must not appear anywhere else, and it must not be used for a page title. Used twice it stops being a register.

---

### 5. Space, radius, elevation

#### 5.1 Spacing
A 4px base with a deliberately shallow ladder, because the client's panels are dense and their rhythm is soft rather than modular:

`4, 8, 12, 16, 20, 24, 32, 48`

- Panel inner padding: 20. Card inner padding: 12 vertical / 16 horizontal.
- Gap between stacked cards: 12. Gap inside a card row: 8.
- The client's page has generous margins around a dense core. Ours keeps 24-32px of page gutter and lets the content breathe inward, not outward.

#### 5.2 Radius
From §3.4 of RAW-FINDINGS. `panel 14 / card 10 / chip 8 / badge 5 / pill 9999`. Circles are reserved for the discipline affordance and avatars, and that reservation is meaningful (P4): if everything is round, round stops meaning "pick one of five".

**Radius must nest.** A 10px card inside a 14px panel is right; a 14px card inside a 14px panel reads as a mistake. Inner radius = outer radius minus the padding between them.

#### 5.3 Elevation
Three levels, and the cool cast is not negotiable (RAW-FINDINGS §3.5):

| Level | Recipe | Used for |
|---|---|---|
| flat | no shadow, `--color-rule` hairline | rows inside a card |
| lift | `--shadow-lift` | cards and panels on the page field |
| press | `--shadow-press` | buttons at rest; they gain `lift` on hover |
| glow | `--shadow-glow` | selected and focused only |

No blur, no backdrop-filter, no glass. No grey-tinted shadow: the client's shadows carry a violet cast, which is what keeps a light UI from looking like office software.

---

### 6. Component anatomy

Each component names the frame it was read from, so a reviewer can check the claim.

#### 6.0 Iconography

**Mandate: filled, rounded, chunky glyphs. Thin line icons are banned.**

The client's icons are small solid 3D renders with real visual mass (`RAW-FINDINGS.md` §3.6). We cannot ship that art, so the substitute must be flat glyphs drawn in the same *weight family*: solid shapes with soft corners, not hairline outlines. A hairline icon set placed on this UI reads as a different product sitting on top of it, and it is one of the fastest tells of a generated dashboard.

The test is concrete: **reduce the glyph to 20px and turn it solid black on white. If it is still legible only because you can see its outline, it is the wrong glyph.**

| Rule | Value |
|---|---|
| Fill | solid, or stroke no thinner than 2.4px at a 24px box |
| Terminals and joins | rounded (`stroke-linecap="round"`, `stroke-linejoin="round"`) |
| Corners on the glyph body | 1.5 to 2px radius, never mitred |
| Optical weight | reads as a filled shape at a glance, not as a diagram |
| Set | one family, drawn to one grid. No mixing a hairline set with a solid set. |
| Forbidden | Lucide, Feather, Heroicons outline, and any imported thin-stroke library |
| Also forbidden | emoji as icons, and 3D renders or clip-art imitating the client's art |

The five discipline glyphs are the client's own objects, and they are the only icons with a fixed identity: **shoe (Speed), heart (Stamina), dumbbell (Power), horn or flame (Guts), book (Wit)**. They appear in the stat band header, on the discipline buttons, and in the gain bubbles, always as the same solid shape at the same weight.

Where a glyph would be generic, use none. A label does the work better than a sparkly shape that could belong to any product (`antislop-ui`, *Generic AI Icons*).

**The one exception, stated so it does not spread.** The sparkle appears in this system for exactly one meaning: `is_unique` on a `Skill`. That is not a decorative choice; the client's own skill icons carry the four-point sparkle (`234521`, `232345`) and the official web paints `decoration_base.svg` sparkle texture onto its buttons (`RAW-FINDINGS.md` §6.1). It is a franchise mark with a single referent.

The rule that follows from this: a sparkle may never mark anything that is not `is_unique`. No sparkle on a button, a heading, an empty state, a "new" badge, or a section title. The moment it decorates rather than denotes, it becomes the generic AI icon that `antislop-ui` names, and it is banned.

#### 6.0b Fixed glyph assignments

The v9 mockup round produced two frames from the same system that used different glyphs for the same concept — Wit rendered as a graduation cap in three frames and as a brain in the fourth, Energy rendered as a heart next to Stamina's heart. Assignments are therefore fixed here, and one glyph carries one meaning (G-42).

| Concept | Glyph | Notes |
|---|---|---|
| Speed | running shoe | the support-card type chip is the same boot, measured in `155016` |
| Stamina | heart | owns the heart; nothing else may use it. The `stamina` type chip is the same heart |
| Power | dumbbell | ⚠️ the measured type chip is a **flexed arm**, not a dumbbell; see the note under this table |
| Guts | flame | ⚠️ collides with burst readiness and occupancy; resolve per D-251. The `guts` type chip is the same flame |
| Wit | graduation cap | never a brain |
| Skill Points | book | |
| Energy | rounded battery with a lightning notch | deliberately **not** a heart |
| Mood | smiley face | ⚠️ collides with the `friend` type chip below |
| Fans / Team | two people | ⚠️ collides with the `group` type chip below |
| Support card type: Pal (`friend`) | single smiley figure, olive on grey | ⚠️ **new collision, unresolved.** The client's own type chip is a smiley person, which is the same shape this table assigns to Mood |
| Support card type: Group | two figures, green on grey | ⚠️ **new collision, unresolved.** Same shape family as Fans / Team |
| Race / Team Race | trophy | |
| Calendar | page with a torn top edge | the torn edge is the motif, not an ornament |

**The support-card type chips are measured, and two of them break G-42.** Deck editor frame
`Screenshot 2026-07-15 155016.png` carries a legend row of seven chips, and six cards in the same frame
identify five of them because the export states each card's type: three `speed` cards wear the blue boot,
two `guts` cards the pink flame, and the `stamina` card the red heart. The remaining chips are the brown
flexed arm (`power`), the dark-green graduation cap (`intelligence`, which the client calls Wit), the
single olive figure (`friend`) and the pair of green figures (`group`).

Three findings follow, and none of them is cosmetic:

1. `power` is drawn as a **flexed arm** on the type chip while `RAW-FINDINGS.md` §3.6 records the HUD's
   3D icon as a dumbbell. One concept, two glyphs, both legitimate, because they belong to different
   render layers. The flat glyph this system draws for Power is the arm; the dumbbell stays only if the
   3D icon is being imitated. Unresolved which the §3.6 list and this table should agree on.
2. The `friend` chip is a smiley figure and `Mood` already owns a smiley face. Under G-42 one of them
   must move, and the resolution is the owner's, not an agent's: the `friend` glyph is the client's and
   cannot change, so the only free variable is Mood's glyph, which this package chose rather than
   measured.
3. The `group` chip is two figures and `Fans / Team` already owns two people. Same class of problem,
   same reason it is escalated rather than fixed here.

The chip **fill** colours are read from the legend row and are not point probes; treat them as
indicative until measured. The glyph identities are not in doubt.

#### 6.1 Button
Two families, and they are not interchangeable.

**Rectangular action button** (`Confirm`, `Reset`, `Close`, `Details`, `Save`; frames `234521`, `232345`, `134106`):
- radius 10, padding 12/24, min-height 44
- primary: `chrome` fill, `on-chrome` text, 1px darker bottom edge, and the **hard-edged sheen** below
- secondary: `raised` fill, 1px `--color-rule`, `ink` text, same sheen
- danger: `crimson` fill, white text. Reserved for run deletion and turn deletion only.
- pressed: `--shadow-press`, translateY 1px
- focus: 3px `green` ring at 35% alpha. The ring is bright green even though the fill is deep green; the ring is chrome.

**The sheen is a spec, not a suggestion, and it is easy to get wrong.** A soft vertical gradient or a blurred highlight reads as a generic CSS button. The client's buttons look like moulded plastic or enamel, which means the highlight has a *crisp boundary*:

```css
/* hard-edged enamel sheen: one crisp boundary, no feathering */
.btn-primary::before {
  content: "";
  position: absolute; inset: 0;
  background: linear-gradient(116deg,
      rgba(255,255,255,.30) 0 34%,
      rgba(255,255,255,0)   34.5% 100%);
  pointer-events: none;
}
```

Rules: the stop is a **split at a single percentage**, not a range, so the edge is hard. The angle is roughly 116 degrees, running from the upper-left corner down toward the lower-right. The sheen covers only the upper-left band, never the whole face and never the bottom edge. It is a fixed 30% white on both themes; on dark it reads as the glass of an indicator lens rather than as a highlight. A vertical `linear-gradient(top, lighter, darker)` is **not** a substitute; that is the generic button and it is the thing being avoided.

**Discipline button** (round; frame `194819`):
- 64px circle, `green` ring 4px, `raised` interior, flat glyph inside, `label` beneath on a coloured band
- selected: ring widens to 6px, `--shadow-glow`, a double chevron appears below
- keyboard: 1-5 select the five disciplines. The hint is printed inside the button at `micro`, because a shortcut nobody knows is not a shortcut.

**Banner button** (the choice shape; frame `124128`):
- white body, 1px accent outline, radius 10 on the left end only
- right end terminates in an **arrow cap**: a 40px gradient wedge of the accent, pointing right
- left end carries a 28px circular accent icon
- `ink-strong` label
- selected: the whole body fills with `green` and the label goes white at large size, or fills `gold` with `ink-strong`. This is the primary component of the guided flow (§8.2).

#### 6.2 Panel
`raised`/`panel` fill, radius 14, `--shadow-lift`, padding 20. Header is a capsule (§6.3) that overlaps the top edge rather than sitting inside it.

#### 6.3 Capsule header
The client's most repeated element (`Log`, `Scheduled Races`, `Choices`, `Sparks`, `Umamusume Details`; frames `234521`, `230755`, `232345`).
- fully rounded pill, height 44, `title` white text
- a **lattice bleed** at the left end: a `#6ABE01` diamond grid, ~64px wide, clipped by the pill
- the client centres the label; **we left-align it** at desktop widths, because a centred pill over a 900px panel wastes the horizontal space a data tool needs (RAW-FINDINGS §7.3)
- **fill is `green-deep` `#4E7906`, not bright `green`.** A capsule always carries a word, so D-3 applies to it without exception. White on `#4E7906` measures 5.17:1; white on the client's `#7FCC09` measures 1.99:1. The bright green survives in the lattice bleed, the border, the focus ring and the nav tile, which is where the client's recognisable lime actually does its work.

An earlier draft of this section specified a bright `#7FCC09` capsule with white text, copied from the client. That was self-contradictory against D-3 and only surfaced when the token set was rendered in a browser at real size. Recorded here rather than quietly corrected, because the failure mode is the one this system keeps hitting: the client's own chrome is decorative and does not meet a legibility bar meant for a tool read for hours.


#### 6.4 Card
`raised` fill, radius 10, `--shadow-lift`, 1px `--color-rule`. Padding 12/16. Selected: `gold` fill with `ink-strong` (8.34:1) plus a 2px `gold` border. Disabled: `disabled` fill, `ink-300` text, no shadow.

#### 6.4b Discipline banner

The client's training banner is two stacked ribbons, and the lower one carries information our design had dropped: the **facility activity name**, which changes with the discipline and the level. Observed in the corpus: `Breaststroke` and `Freestyle` for Stamina at the pool, `Incline` for Guts, `Running` for Speed, `Long-Distance Swimming` for Stamina at Lv5, `Dirt` for Power.

```
Stamina Lvl 1        <- pale ribbon, the discipline and its level
Breaststroke         <- saturated ribbon, the activity the facility offers
```

A mockup showing only `Stamina Lvl 1` is incomplete. The activity line is where the game tells you *what this turn actually is*. It is **not** evidence that facility layouts differ between scenarios — every frame carrying these names is Unity Cup, so the list shows variation per facility within one scenario (D-187 carried the stronger claim until 2026-09-27; see `SCREENSHOT-MANIFEST.md`).

#### 6.5 Stat band

The core readout (frames `194819`, `202142`, `232345`). Six columns, equal width, in one rounded container:
- header strip, height 26: each column carries its own **subtle tint** from the §3.6 table, with a 1px `band rule` line beneath it, and holds icon plus `label` in `ink`. The tints are deliberately near-invisible individually and only read as a group. Skill Points keeps the cyan tint plus the fact that it has no grade badge, rather than the earlier saturated cyan header, which failed contrast at 2.98:1 with white text
- body: per column, a grade badge (§6.7) at left, `numeral-lg` value, and `/cap` at `meta` beneath
- the value is the largest thing in the band. The label is not.
- **Two markers, two meanings.** The bar carries the **1200 soft-cap line**, where *"training gains for stats beyond 1200 are always halved"* (`CONSTRAINTS.md` D-211), and separately the **scenario ceiling**, expressed as base plus bonus (D-212). They are different facts and must not collapse into one line.
- **Why they cannot collapse: one is a behaviour gate, the other is a maximum.** A stat threshold in this game does not just mean "more"; past 1200 the client runs *different logic* on the stat — gains halve — so the line marks where the rules change. The scenario ceiling marks only the largest value that can be held. Two kinds of fact, drawn apart, because folding the behaviour gate into the bar end would hide the one that changes what a Trainer should do next. The same distinction applies wherever a threshold unlocks a named behaviour rather than a quantity; the thresholds themselves are governed by D-283 and stay untraced until a capture or dataset carries them.
- gain bubbles float **above** the column they apply to (§6.8)
- **the bar's end is the scenario ceiling for that stat, not the app's validation bound.** An earlier revision of this bullet instructed the opposite — cap the bar at 1200 and never imply a wider ceiling — and that instruction is withdrawn: it dressed a schema limitation as a design decision (D-31, `ADR-0002`). The denominators are `1,200 + scenarios.json.stats[i]`, so 1400 / 1300 / 1800 / 1900 per scenario and stat, with **2000** as the recorded hard cap, and a Unity Cup Wit bar and a URA Wit bar at the same value must be visibly different lengths. Where a ceiling has components the tool does not store, §6.22's disclosure line says so rather than silently widening the bar.

#### 6.5b Support card rail, failure badge, growth rates

Three elements the client keeps on the training screen permanently and that every mockup in this package so far has omitted.

**Support card rail.** A vertical column on the right edge of the training HUD, one circular avatar per card in the deck. Each carries a small type badge for the card's discipline, a segmented **bond gauge** beneath it, an orange double chevron when friendship training is available, and a flame mark when the card sits on its own tile. It is the Trainer's read on deck state at a glance, and it belongs on the dashboard, not only on the training screen.

**Data source, ruled 2026-09-27: entered by the Trainer, never fetched.** `PRD.md` §6.9 excludes a
support-card database from Phase 1 and `ADR-0005` is still **PROPOSED — not accepted, not built**, so
there is no card table to join. The rail therefore renders what the Trainer recorded for this run: card
name or discipline, bond value as entered, and the two state marks, each only where the turn actually
logged them. Three consequences, all of which have already been this project's failure mode: the rail is
**labelled as manual** wherever it renders, because an unlabelled deck list looks like catalogued data;
a card the Trainer has not entered renders as absent rather than as a placeholder avatar; and no effect
magnitude, level, rarity or growth rate may appear on a rail chip, since the tool holds none of those
(D-20, D-256). Populating the rail from a card dataset is a scope change that needs `ADR-0005` accepted
first — it is not an implementation detail of this component.

**Deck composition editor.** Measured from `Screenshot 2026-07-15 155016.png`, which is the client's own
deck screen and the only frame in this package that shows all six slots at once. Anatomy, top to bottom:

- **Header.** The deck name sits in a green capsule at the left with a pencil control for editing it, and
  `Copy` is a secondary button at the right. The name is the Trainer's, not the game's, so it is free text
  and must not be styled as a stat.
- **Grid.** Two rows of three slots. Each slot carries, in fixed corners: the rarity ribbon at the top
  left, the **type chip** at the top right, the **four limit-break diamonds** at the bottom left, and
  `Lvl N` at the bottom right in display numerals. The corners are the contract; a slot that moves them
  stops being readable at a glance.
- **The sixth slot is the friend slot.** It takes a pink frame and the caption `Friends` under the level.
  Any card may occupy it — the captured deck puts a `stamina` card there — so the caption names the
  **slot**, never the card's type. This is the single most misread thing about this screen, and
  `ADR-0005` exists partly because of it.
- **`Scenario Link`.** A green pill over the lower-left of the art, present on exactly the cards whose
  character appears on the running scenario's linked list. In the frame one card of six carries it, and
  it is the deck's only Unity Cup linked character, so the badge is a join, not a stored flag
  (`UMAMUSUME_REFERENCE.md` §1.4.7).
- **Type legend.** A row of seven chips under the grid — boot, heart, flexed arm, flame, cap, single
  figure, two figures — each followed by `xN` **only when the count is non-zero**. The absent counts are
  the information: a Trainer reads which disciplines the deck neglects.
- **Footer.** `Reset` as secondary, `Auto-Fill` as the single primary action, and a row of page dots
  beneath for the Trainer's saved deck presets.

**Card detail panel.** Measured from the six panels captured the same minute (`155215` through `155337`).
Art at the left with the rarity ribbon and the type chip; at the right the bracket title above the
character name, then `Lvl N / MAX` with `0 SP to next level` beside it, then a `Unique Perk` heading over
the perk name and its own `Lvl N` badge, then the two effect names the perk grants. A tab row below
offers `Support Effects`, `Skills`, `Career Events`, `Flavor Text`.

Two limits on what this tool may draw in that layout, both from the data rather than from taste:

1. **The perk level is not the card level and the perk has no values on record.** A `Lvl 50 / 50` card
   appears with perk `Lvl 30` and a `Lvl 35` card with perk `Lvl 40`, so the two are independent axes.
   `UMAMUSUME_REFERENCE.md` §1.4.7 records that the export carries no perk values and that one of the
   two named perk effects is not even present in the card's effect list. So a detail view may show the
   perk's **name** and its **level** if the Trainer entered them, and must show no magnitude. A number
   there would be invented (D-20, D-256).
2. **Effect magnitudes are computed, and the computation is public.** The client's value at any level is
   the floor of a straight line between the stored anchor levels, verified against the client at three
   levels on five effects. Where this tool displays one, it prints the anchors it interpolated between
   beside the figure, per D-256's rule that a derived value names its own rule.

**Failure badge.** A blue pill directly beneath the stat band reading `Failure 0%` (`194819`). It is the client's own risk surface and it is the natural home for the §6.15 Safe / Caution / Danger bands. Note the limit: the client shows a percentage it can compute from live state; this tool has no sourced curve, so we render the band word and never a number (D-155, ADR-0001 §3).

**Growth rate row.** A row of small per-stat pills, `+ 0%`, `+ 10%`, `+ 20%`, each prefixed by the discipline glyph (`232345`). It is a property of the trainee, not of the turn, so it belongs in the run identity block or the details modal rather than the persistent header.

#### 6.6 Turn chip
The client's torn-page calendar card (frame `194819`), reused as our timeline anchor:
- white body, 2px `blue` outline, radius 10, a `blue` tab strip across the top with two punch holes
- `numeral-xl` remaining-turn count in `blue-700` (5.28:1 with white, and 4.98 against the panel), `label` beside it
- a second, smaller chip stacks under it for the phase countdown
- in the run list this becomes the *identity* of the row: a Trainer scanning runs reads turn depth first, which is what the chip is for in the client too

#### 6.7 Grade badge
- **22px square, radius 5**, `indigo-500` fill, white `micro` letter, 1px lighter top edge
- The badge was 26px in an earlier draft. It came down because the badge is wayfinding and the **value is the subject**: at 26px the letter competes with the numeral sitting beside it, and §4.2 puts numerals at 24px in the band. A grade badge that visually matches the value it annotates has broken P3.
- the client's letters run S through G with `+`/`-` modifiers. We render the letter plus modifier (`E+`, `D+`) and never colour alone (P5)
- **the badge is tinted per grade.** An earlier revision of this section shipped one neutral indigo badge and argued that the hues were already spoken for. That argument was weaker than the observation it overrode: the client tints badges consistently across the stat band, the aptitude table and the rank laurels, and a flat indigo badge throws away a scanning affordance the game spent real design effort on. Restored.

- ⚠️ **The white-letter rule is void on a tinted badge.** The bullet above this one still reads "white `micro` letter", inherited from the indigo draft. It cannot survive the tinting: white on the A orange is about 2.1:1 and white on the S gold is worse, so the rule as written ships nine failing pairs. Tinted fills therefore take a **light fill and `ink-strong` letter**, and the dark theme carries its own darker fill set so the same ink contrast holds. G-5 measures all nine in both themes.

| Grade | Tint family | Measured from |
|---|---|---|
| A | orange | `232345` aptitude column |
| B | pink | `232345` |
| C | green | `232345` |
| D | blue `#6DA0D9` | `202142` |
| E | purple / magenta | `202142`, `232345` |
| F, G | grey-lavender and grey-green | `202142`, `232345` |

The letter always accompanies the colour, so a Trainer who cannot separate the tints loses nothing (P5, D-12). S and S+ sit above A and take a stronger gold. Exact hexes for grades other than D are read from small badges and should be re-sampled before implementation; they are directional, not final.

**The gold collision is real and is accepted under protest.** A stronger gold for S is the client's own convention, and `pick` gold is this system's selection colour. They are kept apart by surface, not hue: a grade gold is always a **small filled square carrying a letter**, and selection is always a **2px outline on an interactive row**. Nothing in this system uses gold fill on a selected row. If that ever changes, the grade tints lose.

Badge tokens, both themes. These are the sanctioned hexes; an artifact using a grade hex not listed here is bypassing the map.

```css
@theme {
  --color-grade-g:  #D6E2D8; --color-grade-f:  #DED9E8;   /* grey-green, grey-lavender */
  --color-grade-e:  #E4D0F0;                              /* magenta       */
  --color-grade-d:  #CFE0F5;                              /* blue, measured */
  --color-grade-c:  #D8EFC8;                              /* green         */
  --color-grade-b:  #FBD0E0;                              /* pink          */
  --color-grade-a:  #FBD9BC;                              /* orange        */
  --color-grade-s:  #FBE9BE;                              /* gold          */
  --color-grade-ss: #F6DFAE;                              /* stronger gold */
}
```

```css
:root[data-theme="dark"] {
  --color-grade-g:  #2C3A2F; --color-grade-f:  #38343F;   /* grey-green, grey-lavender */
  --color-grade-e:  #3E2A4A; --color-grade-d:  #263A4F;
  --color-grade-c:  #2F4220; --color-grade-b:  #4A2838;
  --color-grade-a:  #4A3520; --color-grade-s:  #4A3F22;
  --color-grade-ss: #55481F;
}
```

- grade is **derived** from the value. This section previously said the derivation was
  unsourced and invented an evenly-spaced 150-point banding. **That banding was wrong and is
  replaced**: seven Legacy Select frames supplied real client pairs.

  Ten stat cells read off two frames, each a (value, letter) pair as the client renders it:

  | Value | 75 | 90 | 90 | 95 | 96 | 104 | 107 | 107 | 138 | 143 |
  |---|---|---|---|---|---|---|---|---|---|---|
  | Letter | G+ | G+ | G+ | G+ | G+ | F | F | F | F | F |

  Monotonic, with a hard boundary between 96 and 104. The simplest rule consistent with every
  observation, and with the client's use of `+` modifiers, is a 50-point half-step ladder:

```
G 0-49 · G+ 50-99 · F 100-149 · F+ 150-199 · E 200-249 · E+ 250-299
D 300-349 · D+ 350-399 · C 400-449 · C+ 450-499 · B 500-549 · B+ 550-599
A 600-649 · A+ 650-699 · S 700-749 · S+ 750-799 · SS 800 and above
```

  **State the confidence honestly, and note that it has since been partly falsified.** The one
  measured boundary is G+ / F at 100, and the ladder fits the low range: 436 and 442 both
  predict and render as `C`. It then **breaks**. Run Completion frames add Speed 1245,
  Stamina 442, Power 716, Guts 436, Wit 499, and the ladder predicts 499 as `C+`, 716 as `S`
  and 1245 as `SS`, where the client renders `C`, `B+` and an unexplained `U/G` badge. Two of
  those five are wrong and the third is not on the ladder at all.

  So the 50-point half-step model is **valid below roughly 450 and unvalidated above it**,
  which is the opposite of what a grade display needs, because the interesting values in a
  finished run are the high ones. The ladder stays published and marked `[Provisional]`
  precisely so it is not trusted above its evidence, and §11 carries the open item: a grade
  function needs either the client's real table or a sweep of captures crossing each boundary
  in the 500-to-1300 band, plus a reading of what `U/G` denotes.

  Three things fall out of the same frames. Aptitude letters (`Turf A`, `Sprint E`, `Dirt G`)
  are not on this numeric scale at all; they are a separate character property. The `B+ RANK`
  badge on a Legacy portrait is a third scale again, a score rank. The three must not share
  one token set. And the frames show caps of 1304, 1316, 1318, 1320, 1325 and 1800, which
  confirms §6.22's stack model from client pixels **and contradicts the flat "+16 per ★3"
  breakthrough figure**: 4, 16, 18, 20 and 25 all appear above a 1300 base.

#### 6.8 Gain bubble
The scalloped cloud over a stat (frame `194819`):
- white scalloped shape, 1px outline, a small `+5` inside it, and a larger `+22` below the bubble outside it
- our colour rule, reconciled: **the bubble is a preview, the number below is an applied delta.** The preview inside the bubble is `ink-strong` on the white bubble (it is not yet a fact about the run). The applied delta below it follows §3.3: `orange` for up, `blue` for down.
- the client renders the small preview numeral in a red tone. We do not copy that, because in our system `crimson` means training-failure risk and validation error (§3.3), and a preview tinted like an error would misread. This is a recorded departure, not an oversight.
- the bubble is anchored to the stat column it changes, so cause and effect are visually bound (P3, `RAW-FINDINGS.md` §5.3)


#### 6.9 Log / timeline entry
Frames `234521`. The pattern the run timeline is built from:
- white card, radius 10, `--shadow-lift`
- a 40px circular avatar **overhangs** the top-left corner, outside the card box. The overhang is what makes the client's log scannable by face; ours uses a turn number disc in the same position, since PRD §6.13 rules out portraits
- inside: `label-strong` title, a hairline rule, then `body` prose
- deltas are written as sentences with the direction word coloured: "Stamina went **up** by **14**" with *up* in `orange` and *14* in `orange`; a decrease in `blue`
- entries group under a **collapsible phase bar**: `green` fill, `title` white text at large size (or `green-deep` for AA), a 24px circular chevron at the right

#### 6.10 Choice card (guided input)
Frames `124128`, `124926`. Two-part object, and both parts are required:
- **the option button**: banner shape (§6.1)
- **the preview**: a card beneath or beside it listing every consequence as brown prose with orange/blue deltas, exactly as the client's right-hand panel does
- unselected: preview hidden or muted. Selected: preview at full contrast, card gets `--shadow-glow`.
- **committing is a third, separate action.** Selecting an option never writes data. This is P2 and it is the rule that makes the flow feel like the game.

#### 6.11 Skill row
Frame `234521`. Radius 10, idle fill a `sunken`-to-`idle` vertical gradient:
- 40px square icon, own rounded frame
- `label-strong` name, then `body` description ending in a parenthesised distance or type tag at `meta`
- top right: discount/status badge, `orange` fill with `ink-strong` text
- bottom right: cost stepper, grey minus, white numeral pill, `green` plus
- selected: `gold` fill, `ink-strong` text (8.34:1), 2px `gold-deep` border
- `is_unique` skills get a sparkle mark, a `Unique` text tag at `micro`, and a **pink-to-blue gradient chip fill**, matching the client directly (`232345`, where `Blue Rose Closer` carries a pink-to-lavender gradient against flat lavender-grey on the ordinary rows). An earlier revision used a flat `indigo-50` on the grounds that pink and blue were spoken for; that lost the single most legible affordance on the panel, since the gradient is what makes a unique skill findable without reading. The hues here are **material fills, not semantic colours**, and they do not conflict with §3.3 for the same reason a card background does not. `gold` still means *selected* and is still not used here.



#### 6.12 Modal
Frame `232345`. Rounded sheet over a scrim. The scrim is a flat 45% `indigo-900` wash; the client does not blur. Full-width capsule header (§6.3), stacked hairline-separated sections, one centred secondary Close button in the footer. Max width 720; a modal wider than that stops being a modal and becomes a page.

#### 6.13 Navigation rail
Frames `230755`, `234521`. Vertical rail, 96px wide, icon over `label-strong`, dashed hairline separators. **Active state is a filled tile**: the whole cell becomes `green` with `ink-strong` text, exactly as the client's active Log tile does. No underline, no left border. Inactive cells are transparent with `ink` text.

#### 6.14 Form field
The client has almost no text inputs, so this is our extension and it must not look like one:
- label above at `label`, `ink` (not grey-500)
- input: `raised` fill, 1px `--color-rule`, radius 8, height 44, `body` text in `ink`
- focus: 2px `green` border plus `--shadow-glow`
- error: 2px `crimson` border, message at `label` in `crimson` with an icon. Crimson is the only place red appears (§3.3)
- hint: `meta` in `ink-muted`
- number inputs get steppers, matching the cost stepper (§6.11), so the tool's two numeric idioms are one idiom

#### 6.15 Energy gauge

Energy is the resource that paces a run, so it sits beside the stat band as a persistent element, not inside a turn card. The client's own gauge (`194819`) is the shape: a fully rounded track, dark charcoal unfilled remainder, and a **segmented** fill that lets a Trainer read the level and the threshold at the same glance.

| Element | Spec |
|---|---|
| Track | height 20, radius 9999, `--color-idle` at 40% |
| Fill | **segmented, sweeping the full gauge spectrum**: cyan `#57C6EC` through green `#51E39C` and lime `#87E440` into yellow-green `#B7E23A`, then amber and red toward the right end. Measured as a hue sweep 207 to 75 across the filled portion of `194819`; the montage of partially-filled bars shows the orange and red tail on fuller gauges. The colour encodes **position on the bar**, not the current level |
| Label | "Energy" in its own small pill to the left, as in the client |
| Value | `numeral-md` at the right end, tabular |
| Threshold mark | a 2px `--color-risk` rule across the track at the 50 line, always visible |

The 50 line is drawn from the sourced advisory (`ADR-0001`). It is a **rule on the track**, not a colour change of the fill, because the fill colour already means something else in this system and a green-to-red fill would read as a health bar rather than a resource.

Band colouring is applied to the **value text and a small word**, not to the bar:

An earlier revision described this fill as a cyan-to-lime gradient. That was a measurement artefact: the probe only covered the filled section of a partly-drained bar, so the amber and red tail was hidden behind the dark unfilled track. Corrected.

| Band | Range | Word | Treatment |
|---|---|---|---|
| Safe | above 50 | Safe | `ink` on `green-tint` |
| Caution | 30 to 50 | Caution | `ink-strong` on `gold` |
| Danger | below 30 | Danger | white on `risk` |

The 30 boundary is **an owner ruling, not a game fact.** No source publishes a threshold below 50 (`ADR-0001` §3). Where the Danger band is shown it must be attributable to the tool's own model, and the tooltip or disclosure line says so.

#### 6.16 Advisory and recommendation row

**The advisory is an NPC speech bubble, not a banner.** The client delivers coaching through a circular avatar of a green-clad staff member beside a rounded white bubble (`025519`, `038100`, `038751`, `058814`, `194319`), with a small green `HINT` badge. That is the pattern to adopt, because it converts a warning from an interruption into a piece of dialogue, which is how the game frames every piece of advice it gives.

Adopting it costs almost nothing and buys the single largest immersion win available in this design: the advisory stops looking like validation copy and starts looking like the Trainer's assistant talking to them.

A suggestion is allowed to appear, but it must carry its arithmetic. The accepted form is one short line naming the constant and the stored value that produced it:

> Wit costs 0 Energy and you are at 42. *(GameWith 2026-09-25; source data 2023-02-25)*

Rules: the reason names a number the Trainer can check, the source is cited with its date, and stale constants say they are stale. A suggestion that cannot produce that line does not render. This is how Planner Rule 5's "explainable outputs" survives the scope change recorded in `docs/adr/0001-lift-no-prediction-nongoal-for-energy-guidance.md`.

**When Energy is below 50 the advisory names Wit and Rest specifically**, because those are the two actions the sources describe as the low-Energy response: Wit costs no Energy (§1.1.1) and Rest returns +30 (§1.1.5). The advisory suggests; it never disables, dims or reorders the five options on the basis of a modelled risk (D-155). Rest is offered with its own caveat, since Rest can backfire into 夜更かし気味 (D-205).

#### 6.16b Success and failure branches

An event choice can fail. The wikis document outcomes as split branches, `Choice 2 (Fail)` producing `-3 Mood · 3 Random stat -10 · Practice Poor` (`CONSTRAINTS.md` D-200). The client itself shows a `Failure 0%` badge during training, so failure is a first-class state, not an exceptional one.

A preview therefore renders two blocks:

```
Success
  Stamina  +13      Guts  +4      Skill Points  +2
Failure
  Mood     -2       Last trained stat and two others  -10      Practice Poor
```

- Success block above, failure block below, separated by a hairline. Never merged into one list.
- Decreases stay blue. `Practice Poor` renders as a status chip, not as red text, because red is reserved for risk (§3.3).
- A probability label appears only where the client exposes one. Where it does not, the block reads `Odds not shown` rather than a fabricated percentage.
- A choice that cannot fail shows a single block and the word `Safe`, so the absence of a failure branch is itself information.

This is the one place the mockups of this package were most wrong: every choice preview drawn so far showed a best case and called it a preview.

Failure risk renders as the **band word from §6.16, never a percentage**, unless the owner has enabled the numeric estimate in config. When enabled, the formula and its parameters print adjacent to the figure and the label reads "this tool's model", not the game's. No source publishes a failure curve (`UMAMUSUME_REFERENCE.md` §1.1.5 records only that probability scales inversely with Energy), so a bare "23%" would be an invented statistic.

#### 6.17 Mood tier

Five tiers, pill-shaped, using the tint-and-border pattern from §3.4 so the label stays legible. The pill carries the word always; colour alone never signals the tier. **It also carries the arrow: see D-259, which makes the direction a required part of the component rather than an embellishment.**

**Terminology is no longer blocked.** An owner-supplied capture of the client's own Mood Effect panel (2026-09-27) prints all five tier strings and both effect columns, so the label set is a measured client string set, not an open decision. The panel is the client explaining the system to the player, which is primary evidence of the highest kind available here, and `UMAMUSUME_REFERENCE.md` §1.1.6 now reproduces its figures.

| Tier (client string) | Arrow | Training effect | Pre-race attribute effect | Token |
|---|---|---|---|---|
| `GREAT` | up | +20% | +4% | `--color-mood-great` |
| `GOOD` | up | +10% | +2% | `--color-mood-good` |
| `NORMAL` | flat | 0% | 0% | `--color-mood-normal` |
| `BAD` | down | −10% | −2% | `--color-mood-bad` |
| `AWFUL` | down | −20% | −4% | `--color-mood-awful` |

Three rulings come out of that table, and each one reverses something this package previously asserted:

1. **The arrow direction is the ordinal signal.** Up for the two positive tiers, neutral at `NORMAL`, down for the two negative ones. Because the three provisional colours were derived at the anchors' luminance (§3.5), `GOOD` and `BAD` sit 3.3° apart and `GREAT` and `AWFUL` 1.0° apart, so hue alone cannot order the scale. The arrow is what makes a five-pill row readable, which is why D-259 makes it mandatory instead of stylistic. Of the five glyphs, the `NORMAL` one is the least certain reading from the capture (flat versus no mark at all reads the same at small sizes); the other four are unambiguous, and the rule does not change either way.

**⚠️ This makes pill size a correctness constraint, not a taste one.** The three derived colours are not distinguishable by hue at the luminance they were derived to, so **arrow direction is the only ordinal signal in the component** — a Trainer reading mood at a glance reads the glyph or nothing. Therefore: no mood pill may be rendered smaller than the size at which its arrow is legible, and no layout may drop the arrow while keeping the colour pair to save space.

**Measured 2026-09-28, so the pill ships at a known size and a known pair.** Read off the rendered timeline pills on the run screen, per D-288 (`getComputedStyle` on the element itself, both themes):

| What | Value |
|---|---|
| Ink on each of the five fills | 5.82 (GREAT) / 6.62 (GOOD) / 6.25 (NORMAL) / 6.23 (BAD) / 6.23 (AWFUL) |
| Both themes | identical, because the fills are chrome and are not overridden (§3.4) |
| Ink | `--color-on-mood` `#1F1508`, stepped from `ink-strong` #482720, which is 4.29 on `mood-great` and fails |
| Label size and weight | 12 px, weight 700, `font-mono` |
| Arrow glyph box | 7.3 × 16 CSS px |
| Pill box | 20 px tall; 45.3 / 52.7 / 60.0 / 60.0 / 67.3 px wide for BAD / GOOD / GREAT / AWFUL / NORMAL |
| Padding, radius | 6 px inline, 2 px block; fully rounded |

**The legibility minimum this fixes is 5.82:1, the worst of the five pairs, and it is above the 4.5:1
AA bar for text at this size.** The minimum that was missing from this section is now the size
floor, and the size floor still has no number: 12 px / 700 is what the shipped pill measures, and
whether a Trainer can tell `↑` from `→` at that size is a judgement about a glyph, not a ratio. It
was not measured here, so it is not stated as one. The practical rule is unchanged and now has a
concrete reference: test the five-pill row at the smallest size any surface uses, in both themes,
and treat an unreadable arrow as a rejected layout. Related: G-47 (badge contrast) and D-259.
2. **`Practice Poor` is not a mood tier.** It is a failure *condition* from an event outcome (§6.16b, D-201), and D-203 had floated it as a candidate label for the low tier. The client's low tiers are `BAD` and `AWFUL`. The two vocabularies must not be merged, and a mood widget must never offer a `Practice Poor` state.
3. **The wiki glosses are retired on both counts, the words and the second column.** `UMAMUSUME_REFERENCE.md` §1.1.6 previously printed Peak / Good / Normal / Poor / Worst against a pre-race column of +10 / +5 / 0 / −2 / −5 — the strings are glosses of the JP terms rather than client copy, and that column is not even symmetric across the sign, which is the tell that it was transcribed from prose rather than read off a table. The client prints ±4 and ±2. The training column is the one both camps agree on, so the ±20 % headline is unchanged. D-20's general rule (never invent a client string) is untouched and still binds every other label in the system.

The tier also carries a state marker: the client's panel flags the row the Umamusume is currently on, in the capture at the `GOOD` row. In this system that is a `Current` marker on the row, per D-202's rule that the tier word and the numeric delta are both shown and neither replaces the other.


#### 6.18 Empty, loading, error
Mandatory on every data view (root `CONSTRAINTS.md` C-7, PRD `ARCHITECTURE.md` §7):
- **empty**: a panel with the capsule header, a quiet illustration slot (one motif, §7), a `title` line naming what belongs here, a `body` line saying how to make one, and one primary button. Never a bare "No data".
- **loading**: the panel's real shape with `idle`-filled skeleton rows. The client never shows a spinner over a blank page; it shows the frame you are about to fill.
- **refresh in flight**: catalog reads show last-fetched time from `data_sources` and a quiet progress mark, because the fetch is stale-while-revalidate (`ARCHITECTURE.md` §6). The UI must not imply the data vanished.
- **error**: `crimson` rule on the left of the panel, an icon, the failed source's name, and a retry button. A failed fetch must never blank the catalog (NFR-2), and the UI has to make that visible.

#### 6.19 Fan readout

The client states fan progress as a sentence with the shortfall in it, not as a bare number: `Earn 3000 fans` above `Progress 1,943 fan(s) to go` (`Screenshot 2026-07-14 194819.png`). That is the shape to copy, because it answers "how far am I" without the Trainer doing subtraction against a target they have to remember.

- Persistent in the run header beside the Energy gauge, because fans gate entries and a Trainer checks it before deciding a turn.
- Current total at `numeral-md`, tabular, `ink-strong`.
- Target and shortfall as one `body` line, the shortfall in `up` orange, since fan gain is the reward axis of this tool and orange already means increase.
- No progress bar. The client does not draw one, and a bar implies a linear race to a fixed ceiling when the real calendar has several thresholds stacked.

#### 6.20 Race calendar

**Applies only to scenarios with mandatory race goals.** Ura Finale and Unity Cup qualify. **Trackblazer does not**: it replaces the fixed calendar with Grade Point deadlines and free-form racing, so this panel is absent and a Grade Point progress meter with deadline markers takes its place (`CONSTRAINTS.md` D-221, `SCENARIO-DIFFERENCES.md`).

Measured directly from the client's Scheduled Races panel (`Screenshot 2026-07-17 230755.png`, `235229.png`), which is already a 24-cell half-month grid under a three-segment Junior / Classic / Senior year tab bar. The design formalises that component rather than replacing it.

Cell states, and the count is five because there are genuinely five things a Trainer needs to distinguish:

| State | Treatment | Basis |
|---|---|---|
| Empty half-month | `sunken` fill, muted plus glyph | measured: disabled cell `#D0D1D0` |
| Optional race, enterable | `raised` fill, green plus, race artwork thumbnail | measured: available cell |
| **Mandatory Goal race** | red `Goal` pennant on the cell corner, warm outline | measured: the client's own `Goal` flag |
| Scheduled, already entered | pink `Scheduled` pill over the thumbnail | measured: the client's own `Scheduled` flag |
| **Locked by fans** | dimmed, padlock plus the exact `fans_needed` figure | derived; see the note below |
| **Locked by maiden gate** | dimmed, a distinct marker and the words `Win Debut or a Maiden race first` | derived from §1.2.6 client copy |
| Current turn | pale yellow fill with a warm outline | measured: current cell |

##### Lock state grammar, the three that matter

A race cell can be blocked for two unrelated reasons, and a Trainer who misreads which one has wasted a turn. They must not share a treatment.

| State | Fill | Marker | Text | Why it is distinct |
|---|---|---|---|---|
| **Mandatory Goal race** | `raised`, warm 2px outline | red `Goal` pennant, top-right corner | race name at `label-strong` | Not a lock. It is an obligation, and it reads heavier than an optional race, never dimmer |
| **Fan-locked** | `sunken`, 55% opacity | padlock glyph | `1,000 fans` shown as a **number** | The remedy is measurable. A Trainer can see the shortfall against the header total |
| **Maiden-locked** | `sunken`, 55% opacity, **dashed** outline | distinct conditional marker, not a padlock | `Win Debut or a Maiden race first` | The remedy is an event, not a quantity. A padlock would send the Trainer to check a fan total that is not the obstacle |

The dashed outline on the maiden lock is the whole difference and it is deliberate: opacity and a padlock already say "you lack a number", so the conditional gate has to break the pattern rather than join it. Both locked states keep the race name legible, because a locked race the Trainer cannot identify is not information.

Completed and entered races use the client's own pink `Scheduled` pill (`RAW-FINDINGS.md` §4.9) and are never dimmed.


Two rules that are easy to get wrong:

**A fan lock must show the number, not just a lock.** `fans_needed` bottoms out at 350 for grades 300, 400 and 700 alike, so a lock icon alone cannot tell a Trainer which race they are short on or by how much. Show the figure.

**The maiden gate is not a fan problem.** The client states: "If you don't win the Debut, you're gonna have to win any Maiden Race before participating in any standard races" (`UMAMUSUME_REFERENCE.md` §1.2.6). Dimming it the same way as a fan lock would send a Trainer to check a fan total that is not the obstacle. It gets its own marker and its own sentence.

Tier badges carry the labels Pre-OP, OP, G3, G2, G1, but a badge rendered from stored grade data may only assert G1 and OP with confidence. Codes 200, 300 and 700 are `❌ UNVERIFIED` against those three labels in §1.2.6, so until the mapping is settled the UI shows the tier **as recorded on the race row**, not as derived from a grade code.

---

#### 6.21 Run header, the persistent resource strip

Three values decide every turn a Trainer takes: where the run is, how much Energy is left, and how many Fans have been earned. All three stay on screen at all times, in one strip, in that reading order. Nothing in the run view scrolls them away.

```
[ 13 turn(s) left ]  Rice Shower · Trainee Umamusume · Unity Cup
                     Energy ▓▓▓▓▓░░░░  42  [Caution]   |   1,943 fans · target 3,000
```

| Element | Behaviour |
|---|---|
| Turn chip | torn-page calendar card, §6.6. First in reading order and in the DOM |
| Scenario | `label-strong`, never a subtitle. Four live Global scenarios cap differently, so naming the scenario is load-bearing, not decorative (D-160) |
| Energy | §6.15 gauge. Prominent, because it is the value that can end a run |
| Fan count | §6.19 readout, client sentence form, separated from Energy by a hairline rule |

**Energy sits adjacent to the Confirm control in the guided flow, not merely in the header.** The decision the Trainer is making is "can I afford this session", and a gauge 800px from the button does not answer it at the moment it is asked. When stored Energy is under 50 the Confirm control carries the caution state from §6.15 and the §6.16 advisory line sits directly above it. Prominence at the point of commitment is the requirement; a second copy of the number elsewhere is not.

Fans and Energy are end-of-turn totals in the schema (`ADR-0003`), so the header shows a real stored value rather than a derived sum, and a corrected middle turn cannot silently corrupt the display.

#### 6.22 Scenario identity and stat caps

The tool must never read as a URA-only tracker. Four scenarios are live on Global (`UMAMUSUME_REFERENCE.md` §1.6.1): Ura Finale, Unity Cup, Trackblazer, and Brighter Together Our Grand Concert, each with different caps.

**Scenario identity is a first-class header element**, not a subtitle. The active scenario name sits in the run header at `label-strong` beside the trainee name, and the phase bars in the timeline carry it, because a Trainer running two scenarios side by side needs to know which is which at a glance.

**The cap display has to be honest about being a derived value.** A run's ceiling is 1,200 base, plus the scenario bonus, plus breakthrough raises on inherited ★3 basic-ability Sparks, plus per-stat support-card 「限界値アップ」 effects, applied at three separate moments in the run. The first two terms are stored facts — `scenarios.json` publishes the per-stat scenario bonus directly. The last two are not in the schema at all. There is therefore no single stored cap to print, and the components that are known should be shown as components.

Note the breakthrough term is **not** a flat +16. The client's own finished-run frames show increments of 4, 16, 18, 20 and 25 above a 1300 base (§11 item 3), so the +16 figure that `ADR-0002` amendment 1 carried from a guide is one observed step, not the rule. The disclosure line does not need it — it needs to say the term is untracked — and any later attempt to compute a ceiling from +16 would be building on a disproved generalisation.

So the stat band renders the cap as a **stack, not a number**:

```
Wit  1,340 / 1,800
       1,200 base · +600 scenario · breakthrough not tracked · deck not tracked
```

- The denominator is the sum of the terms the tool actually holds, each labelled.
- **A term the tool cannot observe never renders as zero.** `+0 breakthrough` states a game fact — that this Trainer inherited no ★3 basic-ability Sparks — and the tool has no idea whether it is true, because inherited Sparks are not in the schema. Zero and untracked are different disclosures, and only the first is a number: `+0 breakthrough (no ★3 Sparks entered)` when a Trainer has actually recorded the inheritance, and `breakthrough not tracked` otherwise. The same split applies to the deck term, which is a Phase 1 tool limitation rather than any state of the run. Printing a quiet `+0` is the D-94 failure in numeric form: a figure that looks sourced because it is a digit.
- A disclosure line names what is included and what is not. Until support cards exist in the schema, the line says the deck contribution is untracked. Naming a known gap beats implying a complete figure.
- The bar fill is scaled to the **scenario base for that stat**, never to a global constant, so a Unity Cup Wit bar and a URA Wit bar of the same value are visibly different lengths. This is what makes the tool read as scenario-aware without a single extra pixel of chrome.
- The 1200 validation line is drawn as a separate marker on the bar, not as the bar's end. See `CONSTRAINTS.md` D-31 and `docs/adr/0002`.

| Scenario | Speed | Stamina | Power | Guts | Wit | Source |
|---|---|---|---|---|---|---|
| URA Finale | 1400 | 1400 | 1400 | 1400 | 1400 | `scenarios.json`, corroborated by Game8 Global guide |
| Unity Cup | 1300 | 1300 | 1300 | 1300 | 1800 | `scenarios.json`, corroborated by two post-patch guides |
| Trackblazer | 1200 | 1900 | 1200 | 1200 | 1500 | `scenarios.json` |
| Our Grand Concert | 1600 | 1300 | 1300 | 1500 | 1300 | `scenarios.json` |
| Grandmasters | 1500 | 1400 | 1500 | 1300 | 1300 | `scenarios.json`, JP-only, not on Global |

Every row above is now computed as `1200 + scenarios.json.stats[i]`, so the table is derived from a data field rather than transcribed from a guide. Two consequences for the display: the **hard ceiling is 2000** for every scenario a Global Trainer can currently select, and one JP scenario's Stamina bonus is **negative**, so a ceiling can legitimately sit *below* 1200. A bar component that assumes the denominator is always above the halved-gains line will break on that case.

The earlier version of this table carried a `Trackblazer` row of `1200 / 1900 / 1200 / 1500 / 1200`, transposed from `UMAMUSUME_REFERENCE.md` §1.3.4's JP-named Climax entry. Corrected; see `docs/adr/0002-scenario-caps-exceed-validation-bound.md` amendment 2 and `CONSTRAINTS.md` D-227.

**Scenario coverage, corrected.** An earlier revision of this file claimed the whole corpus was one scenario. That was an overreach from a ten-frame sample and is withdrawn. A 96-frame stratified montage of the turn-chip region shows the corpus is **predominantly Unity Cup** with **confirmed Ura Finale material** (`012428` carries a `Place 1st in URA Finals` goal and a `FINISHED` state). **No Trackblazer or Our Grand Concert evidence was found**, so a three-way scenario comparison is not possible from this corpus.

Unity Cup also turns out to carry scenario-specific resources the design had not accounted for: a `Result Pts` counter and a `TEAM RANK B 11/16` badge (`121214`, `024535`, `031816`). The facility activity names in §6.4b are Unity Cup's own; they show the banner carries an activity line, and nothing yet about how another scenario's facilities are arranged. The cap table above remains documentary, not visual. Scenario-specific chrome variations must be designed from the scenario data, or captured from the client, before any of them is asserted in a mockup.


---

#### 6.23 Scenario-composed resource strip

The strip is the mechanism by which the app becomes scenario-aware rather than scenario-decorated (§6.22, `CONSTRAINTS.md` §10n). It sits in the run header below the identity row and holds **zero to five** items: always turn, Energy, Fans; then per scenario Team Rank and Spirit Burst count, or Grade Points and Shop Coins.

- **Composition rule.** Items are declared per scenario and rendered in a fixed priority order — clock, then the scenario's own currency, then Fans — so the strip's *shape* changes and its *positions* stay stable within a scenario. A Trainer running two Unity Cup careers sees the same layout; a Trackblazer run does not inherit the Team Rank slot and find it empty.
- **An empty slot is never rendered.** A reserved-but-blank gauge tells the Trainer the mechanic exists and they have none of it. Absent beats empty, and this is why the strip must be flexible-width, not a fixed grid.
- **One flex row, no fixed widths.** Numerals range from three digits (`450`) to six (`120,000`) and labels from `Energy` to `Grade Points`. The container must therefore be `flex` with `gap`, never `grid-cols-5`, or a Trackblazer Grade Point count and a five-figure fan count will collide at 1280px. This is the specific layout risk the scenario work introduces and the reason the strip is its own component rather than header chrome.
- **Baseline is a real configuration, not a fallback.** A URA Finale run shows three items and that is correct — the scenario has no team system, no shop and no currency. An undescribed fourth scenario renders these same three plus its own caps (§10n, D-241).

#### 6.24 Unity Cup: Team Rank gauge and Spirit Burst roster

**Team Rank** is a letter, not a meter: `G F E D C B A S S+`. It is rendered as a letter chip per stat plus one aggregate rank, because facility level derives from the *per-stat* rank while the "It's On!" reward derives from the *aggregate* — two different consumers of one widget, and the reason `S+` needed its own visual step even though facility level stops rising at `S` (§6.22 marker, D-222).

**Spirit Burst roster** is a compact list of teammates, each carrying one of **six** states: chargeable, charged, held, normal-spent, **Extreme-chargeable**, Extreme-spent. Requirements the states impose on the anatomy:
- normal and Extreme must be distinguishable **at a glance and by text**, since Extreme is the higher reward and the 0%-failure guarantee — a colour-only difference fails P5 and D-6.
- a spent-normal teammate is *not* inert: they are the next Extreme candidate. Any "consumed" treatment (reduced opacity, strikethrough) is a lie on the fifth state.
- **no energy penalty is depicted on Special Training.** The pre-2026-07-01 rule that bursts raise energy cost except on Wit is dead; the only surviving energy interaction is a Wit burst granting **+5 extra recovery** (§6.15, D-223).
- the burst counter in the strip counts **normal plus Extreme combined**, because that is what the reward ladder reads (D-223).
- where an Extreme burst or a Good-Luck Charm sets a facility's failure rate to zero, the facility's risk affordance is **removed**, not downgraded to "low" (D-231).

**Occupancy is content.** Facility tiles show how many teammates are present, because the multi-uma bonus scales across 2/3/4/5 flames and that number is the reason to prefer a crowded tile over a 1-on-1. The client's Unity Training overlay hides skill-hint icons underneath; the tool must surface a hidden hint rather than reproduce the collision (D-229).

#### 6.25 Trackblazer: Grade Point meter, shop counter, Epithet tracker

- **Grade Point meter** reads against the *current* objective only, with the deadline date and a standing note that surplus does not carry forward. A cumulative total would advertise a banking strategy the rules forbid (D-232).
- **Shop counter** pairs the balance with **turns until the rotation resets**, and the balance is the lesser of the two. The in-game cap of five copies per item and the overwrite hazard — a weaker multi-turn item bought while a stronger one is active destroys it — are both purchase-time facts, so they belong in the shop step, not in a tooltip nobody reads (D-232).
- **Epithet route tracker** is a won-of-needed checklist per route (`2 of 3`), because routes are what shape the racing schedule and the rewards are stat and hint gains. Keep it as a list, not a grid of cards: there are dozens of epithets and only a handful of live routes, and an equal-sized card wall is the §6.15-of-layouts mistake.
- **Race Fatigue chip** shows consecutive races run and its consequence. It is the one place a real percentage may print, and it must vanish after Late December (D-230, D-231).
- **No Race Calendar.** Its absence is the scenario's defining UI fact. The slot is filled by the Grade Point meter and a free-form race log (D-221).

#### 6.26 Rarity vocabulary must not collide with state colour

The scenario guides describe rewards as **gold-rarity** and **white-rarity** skill hints, and `pick` (`#EFC96A` / dark `#F5B73C`) is already the **selected** state in this system. Reusing it for "gold rarity" would make every rewarded item look pressed and every selected row look like a prize, and the collision is invisible to anyone not reading the tokens.

So: rarity is written as a **word**, never as the selection colour. If a visual marker is needed it is a neutral outline or a rank letter, and the selection treatment stays exclusive to selection. The same test applies to the client's own gold-text cap-increase signal (D-213): that one is legitimate because it marks a *value*, sits inline in numerals, and never appears on an interactive surface — three conditions the rarity case fails.

**Scenario currencies get no identity colour.** Grade Points, Shop Coins, Team Rank, Result Pts and Spirit Burst counts are glyph-plus-label-plus-numeral, matching how the five stats are already handled (§3.6, D-114). Each new currency could claim a hue; four of them would blow the dose cap in §7.1 and none of them carries meaning that colour could add, since the Trainer's decision depends on the *number*, not the pigment.

#### 6.27 Legacy Select

The pre-run screen where a Trainer fixes the two Legacies a Trainee Umamusume will inherit
Sparks from. Sourced from seven client frames captured 2026-07-15
(`docs/game-screenshots/Screenshot 2026-07-15 1538*.png`, `1539*.png`, `154010.png`), read at
crop resolution rather than from a description, and cross-checked against Game8's Global
Legacy and Sparks guide and the umareference inheritance chance tables.

**Layout.** A two-panel composition, which is the client's desktop grammar (§3.8) rather than
a choice: the left panel holds the Trainee's stat band, aptitude table and the two Legacy
slots over the scene; the right panel is a full-height Sparks list under a capsule header.
Left is decision, right is consequence. This is the one place the persistent right-hand
region legitimately holds data rather than navigation, because it is a detail pane of the
selection on the left, not a destination.

**Two numbering systems, both client-authored.** The left slots read `Legacy 1` and
`Legacy 2`; the right panel groups read `1st Legacy` and `2nd Legacy`. Reproduce both
verbatim. Normalising them to one style would break matching a label on screen to a label in
the tool, and inventing a third would be worse.

**Spark colour coding, measured from the bars themselves.**

| Kind | Bar fill (measured) | What it carries |
|---|---|---|
| Stat | `#3CB4F0` / `#60C0F0` blue | Speed, Stamina, Power, Guts, Wit |
| Aptitude | `#FC84B4` / `#FC78B4` pink | Track, Distance and Style aptitudes |
| Unique skill | `#90CC30` green | The ancestor's Unique Skill, e.g. `Red Shift/LP1211-M`, `Triumphant Pulse` |

Blue and pink here are **category identity**, not direction. That is a real collision with
§3.3, where blue means decrease and pink carries no meaning, and with the delta pair this
package had to step darker for the light theme. The resolution is containment: the spark
fills are large bars with white text on them, they appear only inside the Sparks list, and
no delta, badge or status control may reuse them. A pink bar must never be able to read as
"this went down".

**Star rating.** Three slots per spark, filled gold against empty grey. The count is the
spark's rank and it drives the inheritance chance, so it is data, not ornament. It must never
be the only signal: the row carries the rank numerically on request, and the filled/empty
shapes differ in silhouette as well as colour (P5, D-12).

**Affinity is a header pill, not a between-slots gauge.** The client renders
`Affinity:` followed by a concentric-ring mark, on a pink-to-orange pill, in the panel header
beside the `Skills` button, above the stat band. It describes the Trainee's relationship to
the selection as a whole. Placing it between the two Legacy slots, which is where a designer
would reasonably guess it goes, is wrong and would imply it is a property of one pairing.

**Guest Legacy.** A borrowed friend Umamusume is marked with a `Guest` pill over the portrait.
It is read-only: the Trainer cannot alter another player's Umamusume, so every control that
would mutate it is absent rather than disabled-grey. Borrowing is free once a day and costs
Monies after that, which is the game's economy and not this tool's; the tool records only
that the Legacy was a guest.

**Rank badge.** Each Legacy portrait carries its own letter rank in the client's badge
treatment, for example `B+ RANK`, plus a ring whose colour denotes the card's rarity. That
badge is the ancestor's, not the Trainee's, and the two must not be confused in a list.

**What the tool must not do.** Inheritance is a **roll**, and the rates are published:

| Spark kind | ★1 | ★2 | ★3 |
|---|---|---|---|
| Stat (blue) | 70% | 80% | 90% |
| Aptitude (pink) | 1% | 3% | 5% |
| Unique skill (green) | 5% | 10% | 15% |

Second-generation Sparks have their rates **halved**, and affinity acts as a percentage
multiplier on those chances. None of this may be presented as a predicted outcome. Planner
Rule 1 forbids simulation and Rule 4 requires every number to be a pure function of what the
Trainer entered, so the tool shows the **chance the game publishes** and the **result the
Trainer observed**, and never a projection of what a run will inherit. A row may read
`Stat spark, 90% chance` because that is a sourced constant. It may not read `likely to pass
Speed` because that is a guess about a random event.

**Data model reality check.** `training_runs` carries `inheritance_parent_a_id` and
`inheritance_parent_b_id`, both nullable FKs to `umamusume`. That records *which character*
was chosen and nothing else. It cannot record the second Legacy's own ancestors, the affinity
value, the Guest flag, the star ranks, or which Sparks actually came through. The screen
described above therefore has more state than the schema can hold; `ADR-0003`'s `turn_events`
payload pattern is the natural home, and closing that gap is a schema proposal, not a UI
decision.

**Terminology conflict, unresolved and needing a ruling.** The owner instruction bans
"parent" in favour of "Legacy" or "Ancestor". Game8's Global guide glosses Legacies as
"(also known for parents)", the client's own second-generation grouping is what English
guides call grandparents, and the shipped columns are literally
`inheritance_parent_a_id`. UI copy can honour the ban today at no cost. The **column names
cannot**, and renaming them is a migration, which is out of this phase's scope. Recorded
rather than silently half-applied.

#### 6.28 Run Completion state

The summary the client shows when a career ends and the Trainee becomes a Veteran Umamusume.
Sourced from six frames dated 2026-07-15 (`Screenshot 2026-07-15 0622*.png`, `0623*.png`,
`0625*.png`), read at crop resolution. The example run is `[Peak Blue] Daiwa Scarlet`,
Career Rank **A**, Rating **10,884**, Fans **396,191**.

**6.28.1 Career Rank medal.** A circular medal: laurel wreath border, a coloured disc, the
rank letter at full height in white, and a ribbon across the lower third reading `RANK`. The
disc colour tracks the rank (observed orange-red for A). Beneath it, `Rating` and the number
in monospace tabular figures. Below that, a horizontal progress bar between two smaller medals
showing the current rank and the next one, `A RANK` to `A+ RANK`, with the filled portion
indicating progress.

This is the run's score and it is the largest element on the screen. **The rating is not
computable here.** Career Rating in the client is a function of final stats, race results and
fans across the whole run, and reproducing it would be a simulation, which Planner Rule 1
forbids. So the tool records the rank letter and the rating number the Trainer read off this
screen, labels them as entered, and never derives or forecasts them. A run may be saved with
an empty rating; it may not be saved with a predicted one.

**6.28.2 Career Record and Major Wins.** Two green section bars, `Career Record` and
`Major Wins`, each with the double-slash mark at its right end. The record line reads
`Races: 18   Wins: 16`. Major Wins is a list where the **icon encodes the tier of the
achievement**, and the two kinds are visually different: a gold medal disc for a series title
(`Senior Autumn Triple Crown`, `Triple Tiara`) and a blue `G1` tier badge for a single race
(`Yasuda Kinen`).

The rule that follows is a content rule, not a visual one: **a win title may only be rendered
if the race exists in the scenario calendar data.** `Triple Tiara` and `Triple Crown` are
composite achievements rather than races, so they have no row in a race table and must come
from a curated list, not be assembled from race names. Inventing a title because it sounds
plausible is the same failure as inventing a skill name.

**6.28.3 Final stats and aptitude grid.** The stat band returns in its list form, five columns,
each a grade badge, the label, and a monospace value: Speed 1245, Stamina 442, Power 716,
Guts 436, Wit 499. Below it the aptitude grid, three rows labelled `Track`, `Distance`,
`Style`, each cell a letter badge: Turf A, Dirt G, Sprint F, Mile A, Medium A, Long B,
Front A, Pace S, Late D, End G.

A radar chart appears in the client as an alternative view behind a swap control. It is
optional here and the list is mandatory, because the list carries the numbers and the chart
does not (D-94: a chart must answer a question, and "which of my five stats is weakest" is
answered faster by five numerals).

**Measured badge palette.** Confirmed from these frames, correcting two entries in §6.7's
table: `A` orange, `B` and `B+` pink, `C` green, `D` blue, `F` **periwinkle**, `G` grey,
`S` gold. **F is not grey.** It is the same violet-blue seen on the Legacy Select frames, and
grouping F with G, as an earlier draft did, removes the one distinction that makes the ladder
scannable.

**The `U/G` badge, unexplained.** Speed 1245 carries a badge that is not a letter on the
G-to-SS ladder: a large violet `U` with a smaller grey `G` set at its lower right. The same
mark appears on a Legacy portrait in the §6.27 frames. What it means is **not established**.
It is almost certainly a tier above `SS`, but it could be a different axis entirely. Do not
render it, do not map it to a number, and do not invent a meaning for it. Recorded as an open
item in §11.

**6.28.4 Class progression pyramid.** A gold-gradient triangle listing the fan classes bottom
to top, each with its threshold in fans on the right:

| Class | Fans |
|---|---|
| Debut / Maiden | entry class |
| Beginner | 1st place |
| Bronze | 5,000 |
| Silver | 20,000 |
| Gold | 50,000 |
| Platinum | 100,000 |
| Star | 160,000 |
| Top Star | 240,000 |
| Legend | 320,000 |

The reached tier must be marked, not inferred from a number: the client flags `Legend` with a
`KEEP !` pointer because the run is above its threshold and the tier must be defended. The
line below reads `Fans 396,191 (+51,012)`, the gain from the final race in the same
increase-orange used for every other gain, so the delta convention holds here.

**These thresholds are a fourth, separate fan scale.** Race entry gates run 350 to 25,000, URA
event gates 60,000 / 70,000 / 120,000, Trackblazer's Unique Skill gates 5,000 / 60,000 /
120,000 paired with bond, and the class ladder is the nine tiers above. D-224 forbids merging
two of them; this adds a fourth. A single "Fans" readout serves none of them, and the class
ladder is the one that matters at run end.

**6.28.5 Race result header, and a new tier.** The final race screen shows `EX` beside
`Twinkle Star Climax Race 3`, then `Nakayama Turf 2000m (Medium) Right / Inner`, a `Firm`
going chip, and a large `1st` placing medal under a `Placing` label.

`EX` is a **sixth tier label** beyond Pre-OP, OP, G3, G2 and G1, and it had not been recorded
anywhere in this package. It confirms that the tier set is not closed, which is why D-153
forbids deriving a tier from a grade code: an unseen code would be silently mislabelled. The
`EX` badge also appears on the Legacy Select frames, so it is a general class marker rather
than something specific to the Climax.

### 7. Motif and ornament budget

The ornament list is in RAW-FINDINGS §3.7. What matters here is how much of it survives into a tool:

| Motif | Use | Budget |
|---|---|---|
| Argyle lattice | left bleed of capsule headers | one per panel header, never elsewhere |
| Faceted page field | `--color-page` background | full page, ~4% contrast against the base. It must be nearly subliminal. |
| Sparkle | `is_unique` skills **only** | never on a button, never on run completion, never decorative. See §6.0. |
| Laurel | rank badges only | |
| Ribbon / banner | choice cards, phase bars, section heads | the shape, not the gradient costume |
| Arrow cap | choice cards, next affordances | |
| Double chevron | "this one is boosted/active" | one on screen at a time |
| Radial glow | behind the run identity block | once per screen |

**Budget rule: at most two motifs per visual region.** The client gets away with density because a character model occupies the middle of its frame and the ornament sits at the edges. We have no character model, so the same density would read as noise. If a region has a lattice and a ribbon, it gets no sparkle.

#### 7.1 Colour dose caps

The palette is wide because it is semantic, but a *screen* must not be wide. The client's own frames carry one saturated hue at a time against a near-white field, and that restraint is the whole effect.

| Cap | Rule |
|---|---|
| Saturated hues per screen region | 2, plus `green` as the standing chrome colour |
| `green-500` (bright) | chrome only: capsule fills, borders, rings, nav tile, lattice. It is the brand constant and is exempt from the count. |
| `crimson` | appears on at most one element per screen. If two elements want it, one of them is not a risk. |
| `gold` | selection only, and one selection at a time |
| `orange` + `blue` together | allowed, because a delta list legitimately shows both directions |
| Gradient | functional only (the idle-to-sunken card fill, the arrow-cap wedge). Never a decorative gradient sweep across a panel or the page. |
| Glow | selected and focused only, max 2 on screen |
| Texture | the faceted page field, at ~4% contrast, plus the lattice bleed. Nothing else gets a pattern. |

A reviewer's quick test: squint at the screen. If several hues compete for attention, the cap is broken, and the fix is to make one of them neutral rather than to add spacing.


---

### 8. UX architecture

Full layouts, states, and acceptance conditions are specified in `docs/design-research/CONSTRAINTS.md` §4-§7, which is the reviewable contract. This section records the intent behind each.

#### 8.1 Trainer's Dashboard (Screen A)
The client's own two-window composition (scene left, working panel right) is the frame. Left: the run identity and the persistent stat band, which is always on screen because a Trainer reading a turn needs the totals the way the client keeps them on screen during training. Right: the turn timeline as a stack of §6.9 entries, grouped into collapsible phase bars, newest last. The turn chip (§6.6) anchors the top-left and is the persistent "where am I" object.

The timeline is the product. Everything else on the dashboard exists so a turn can be read in context: the stat band shows where the series landed, the skill panel shows planned-versus-actual (US-4), and provenance sits at `meta` weight because it is a fact about the catalog, not about the run.

**The question the dashboard answers is: "how did this stat get to where it is?"** That is why the primary visual is a turn-ordered entry list with per-turn deltas, not a line chart. A chart of six series over 24 turns answers "where did the curve go", which is a question a Trainer cannot act on: they already know the shape, they want the turn that caused it. The stat band plus the delta-bearing timeline answers the real question, and no chart ships on this screen unless it is retitled to a question a line actually answers (`CONSTRAINTS.md` D-95).


#### 8.2 Guided Turn Input (Screen B)
This is where the brief's "not a web form" requirement lands, and the client already contains the answer: the Choices panel. A turn is logged as a sequence of decision cards, each one asking a single question in the game's voice ("Which training are you focusing on this turn?"), each showing its consequence preview, and each committing only on an explicit confirm.

The critical property: **the preview is the input.** The Trainer is not filling in six number fields and hoping; they are picking the thing that produced the numbers they can see. The fields exist as an escape hatch for paste-and-correct, not as the primary path.

Energy enters this flow as a **logged value plus an advisory row**, not as a gate. The gauge is persistent in the header so the Trainer knows their position before choosing, and when stored Energy is under 50 the step shows the §6.17 advisory naming Rest, Outing and Wit as the game's three documented mitigations. What the flow must not do is block, dim, or auto-reorder the five options on the basis of a modelled risk: that turns an advisory into a prediction the sources cannot support. The Trainer picks; the tool explains.

**No numbered step sidebar.** A vertical 1-2-3-4 rail with a highlighted current step is the shape of a SaaS setup wizard, and it is the single fastest way for this flow to stop feeling like an in-game event. The client has no such rail: an event screen presents one question and its options, and the only progress cue is the turn counter. So the flow carries a single `Step 1 of 3` line with three dots at the foot of the card stack, and nothing else. Where the steps genuinely need naming for orientation, they are named *inside* that line, not in a column beside it.

**Deck awareness, and the hard limit on it.** The discipline step asks "Which training are you focusing on this turn?", and a Trainer is answering that question with their deck in mind: which cards sit on that tile, whether their gauges are past 80, and whether the tile is where a card's own discipline is. So the option row carries the deck's chips for the chosen discipline — the §6.5b rail's data, reused here rather than redrawn — because the deck is the reason the choice is good, and a step that asks the question while hiding the answer is a form, not a decision.

What the step must not do is turn that into a recommendation. Three of the inputs to "which tile should I pick" are **not stored anywhere in this application**: the per-card friendship gauge at the start of the turn, the Specialty Priority roll that decides whether a card actually appears on its own tile, and the per-tile participant count. Each is either Trainer state the tool never records or a random draw the tool must not simulate (Planner Rule 4, PRD §6.11). So the deck row is **descriptive and inert**: it names the cards whose discipline matches the option, and it stops there. No ordering of the five options by deck strength, no "recommended because three cards are here", no highlighted option. If the owner later accepts `ADR-0005` option 2 and the deck becomes stored data, the row may add counts, and still no ranking, because the roll underneath it is not modelled.

#### 8.3 Run list (Screen C)
Cards, not a table, because the turn chip wants to be an object. Each card: trainee name (`title`), scenario, status pill, turn depth chip, the **micro-grade strip**, and the skill plan tally as Suggested/Acquired/Skipped counts. Sort and filter are `label`-weight controls in the panel header, never a toolbar that outshouts the data.

**The micro-grade strip** is what makes a six-card grid scannable. A Trainer deciding which run to open is asking one question, "how good is this build", and reading five four-digit numbers per card to answer it does not scale.

- Five 18px grade chips in the fixed stat order Speed, Stamina, Power, Guts, Wit, no values, no labels. The order is the only thing that identifies which chip is which, and it never varies.
- Each chip carries the §6.7 grade letter at `micro` in the §3.6 band tint as its background, so the strip also repeats the column identity used on the dashboard.
- A 4px dot inside the chip's lower-right corner marks a stat that has broken past 1000, because that is the threshold a Trainer actually cares about and it is derivable from the stored values.
- The strip is `aria-hidden` and the same information is offered as text to assistive technology: the card carries a visually-hidden line reading "Grades: Speed D, Stamina D, Power E+, Guts D, Wit F." A pattern that only works by sight fails P5 and D-12.
- Values stay on the card at `numeral-md`, below the strip. The strip is a shortcut, never a replacement.

The strip is deliberately not a sparkline and not a radar chart. Both answer "what shape is this build", which is not the question at this size, and a chart in a card is the `CONSTRAINTS.md` D-94 failure with extra steps.

#### 8.4 Skill search (Screen D)
FR-D-2 asks for autocomplete on normalised keys. The client's skill row (§6.11) is already a search result, so the search surface is a list of those rows under a single field. Matching is on `match_key`, so a query for a Japanese name can return an English row; the result must show which field matched, because a Trainer needs to know whether they found the skill or found an alias of it.

---

#### 8.5 Dynamic event resolution

A turn is not always "pick a training". Five things can happen, and the UI must present each as itself rather than as another form variant. The classification below is the design's own; the sourced backing for each row is stated, and where the source is thin the design stays generic on purpose.

| Flow | Trigger | What the screen does | Source backing |
|---|---|---|---|
| **F1 Standard turn** | No event fired | The five banner options, preview, confirm | `UMAMUSUME_REFERENCE.md` §1.1.2 |
| **F2 Character event** | Round-triggered or random, specific to the trainee | Event panel slides in over the scene: narrative, then A/B/C choices each with previewed deltas | `training_events__char.json`, 135 chains (§1.4.4) |
| **F3 Support card event** | Trainer occupied a tile with a card on it | Same panel shape; the header names the card and the outcome list adds Bond and hint effects | `training_events__friend.json` (11 Pal), `training_events__group.json` (5 Group) (§1.4.4) |
| **F4 Scenario event** | Mandatory, scripted by the scenario | A single-choice panel, or a multi-step scenario sheet when the event genuinely has parts | §1.1.2, §1.6.1, described qualitatively only |

**Random world events are not modelled as a separate flow.** No in-run dataset in the reference supports a category distinct from the three chains above, and §3.4 "recurring event types" is live-ops content on a different axis entirely. If such events exist they land in F2 or F4, and the `TurnEvent` row carries a free-text origin so the data does not lie about a taxonomy it does not have.

#### 8.6 The event panel

F2 and F3 share one component, and it is the client's own object (`Screenshot 2026-07-14 124128.png`, `124926.png`), not a web modal:

- **The scene stays.** The panel is an overlay on the left region; the training scene, the turn chip and the stat band all remain visible behind it. A scrim that blanks the page destroys what the client is careful to preserve, which is the Trainer's sense of where in the run they are.
- **Tag ribbon, not a title bar.** A cyan parallelogram tag names the origin (`Support Card Event`, `Character Event`), and a wider blue gradient ribbon below it carries the event title in white bold. Both are slanted at one end.
- **Narrative block** in `body` at generous line height, with the speaker named above it.
- **Choices are §6.1 banner buttons**, and each carries its own preview showing every consequence before commitment. Amended 2026-09-28: the old clause "never radio inputs" was written when a radio meant a row of form widgets, and it is now the opposite of what the component needs. The built rail renders `<input type="radio" name="choice">` inside the banner, so the group tells the truth about itself (KI-14, `slice-5-2026-09-28.md` §5): the banner is the styling, the radio is the semantics, arrow-key roving and selection are the platform's, and a choice reaches the request on submit, which is what makes the two-stage preview work without a script. What stays banned is a bare radio row where a decision card belongs, and a `role="radio"` claim on an element that does not implement it.
- **Delta colours are the client's**: gains orange, losses blue, never green and never red.
- **A cap increase is never phrased as a stat increase.** The client separates raising a value from raising its ceiling: choice previews read `Max Energy +4` against `Guts +10` (`124128`), and §1.3.4 names the effect class 「限界値アップ」, Global `Max Speed`, `Max Stamina`. A log line reads `Speed cap went up by 4`, never `Speed went up by 4`. Exact client log phrasing is unobserved, so the wording is ours; the distinction is not negotiable.
- **Confirm is a separate action** and is the only thing that writes.

#### 8.7 Scenario sheet

F4 is the one place a stepped flow is allowed, and only when the event genuinely has multiple parts. A scenario sheet may show a step list; a standard turn may not. The sheet is a wider version of the same panel with the scene still behind it, and it keeps the tag ribbon, the banner choices and the preview-before-commit rule. It does not become a wizard with a progress rail and a Back / Next footer, because that shape signals software onboarding rather than a game beat.

The sheet must be **data-driven, not scenario-specific**. The summer camp mechanic is disputed between the brief and `UMAMUSUME_REFERENCE.md` §1.1.2, which says every discipline is set to Lv5 at once for the four-turn window rather than the Trainer picking two. Encoding either version into the UI would bake an unverified rule into the interface, so the sheet renders whatever steps and options the scenario record supplies and the mechanic question is raised separately rather than answered by a wireframe.

#### 8.8 TurnEvent, and what it costs

Recording which event fired and which choice was taken needs a table the schema does not have. Proposed shape, mapped to the flows above:

| Column | Purpose |
|---|---|
| `training_run_id`, `turn` | locates the event inside the run, same key as `TurnEntry` |
| `event_type` | `Character` / `SupportCard` / `Group` / `Scenario`, enum-backed |
| `source_name` | the event title as displayed |
| `choice_index`, `choice_label` | which option the Trainer took, and its text at the time |
| `deltas` | json, the stat, SP, Energy and Mood changes actually applied |
| `support_card_name`, `bond_delta` | nullable, F3 only |
| `origin_note` | nullable free text, so an event that fits no category is recorded honestly |

This is a schema addition and it needs two things before it can be built: a PRD requirement to cite, and Architect sign-off. `AGENTS.md` states the rule plainly, every new table must cite a PRD requirement, no citation no merge. Nothing in FR-A through FR-E currently covers event logging. The nearest anchor is US-3, the promise to keep the history a spreadsheet used to, and a spreadsheet that logs training runs does record events, so the citation argument is reasonable. It remains the owner's and the Architect's call rather than something to assume.

Note also that `deltas` stores what happened, which keeps it inside Planner Rule 4. It is not a second copy of the stat series and must not drift from `turn_entries`: the reconciliation rule is that `turn_entries` holds absolute values and `TurnEvent.deltas` is derived detail, so any mismatch between them is a defect with a named owner.


#### 8.9 Race selection, F5

A fifth flow, added because the race calendar is a decision point and not just a display. It appears **only on a turn where the calendar holds an entry**, and it reuses the §8.6 event-panel shape rather than introducing a new component: same banner buttons, same arrow-caps, same preview-before-commit.

The step is **conditional**. It renders only on a turn where `scenario_races` holds an entry for that slot; on every other turn the flow goes straight to training or rest. A race step on an empty slot is noise that trains the Trainer to ignore it.

Each race option shows: race name, tier badge, `fans_needed` against the current fan total, whether it is mandatory, and the Energy cost. A fan-locked race is visible but disabled with its number shown, never hidden, because a race the Trainer did not know existed cannot be planned for. The preview lists the fan gain and the placement-dependent outcomes. **Skip is a first-class option**, rendered as a banner like any other, because "I am choosing not to run this" is a decision the Trainer makes deliberately and it should not be the absence of a click.

The panel must not rank races, project a placing, or estimate a win. `PRD.md` §6.11 still forbids race prediction and this flow does not change that; the Energy and `TurnEvent` decisions in ADR-0001 were scoped to training, explicitly leaving race outcomes untouched. Eligibility is arithmetic over stored values and is fine. Outcome is not, and does not appear.


---

### 9. Motion

The client is a 3D game with idle character animation; a data tool cannot and should not imitate that. What transfers is the *feel* of the UI layer: snappy, physical, never slow.

| Interaction | Spec |
|---|---|
| button press | 120ms, translateY 1px, shadow lift to press |
| card select | 140ms ease-out, glow ring fades in, fill crossfades |
| choice card expand | 180ms, preview slides 8px and fades; the card does not move |
| commit | 200ms; the new turn entry pushes in from the bottom of the timeline and the stat band's gain bubble fires first |
| panel open | 160ms ease-out, scale 0.98 to 1 plus fade. No slide-in sheets. |
| phase collapse | 180ms height, 120ms chevron rotate |
| number change | count-up over 240ms, only on the stat band |

Rules: nothing animates that is not responding to the Trainer. No idle motion, no ambient drift, no entrance animation on page load (a local tool is opened hundreds of times a day). No motion longer than 240ms. `prefers-reduced-motion: reduce` collapses every transition above to a 1ms crossfade and disables the count-up, keeping the state change legible without the movement.

---

### 10. Accessibility

- Contrast: every text pair in §3.4 passes at the stated level. The chrome steps are not used for text. This is the direct response to the client's own failing chrome (§3.4) and it is the one place we knowingly depart from the source.
- Focus: 3px `green` ring at 35% alpha, always visible, never `outline: none` without a replacement. The ring is bright green because it is chrome.
- Target size: 44px minimum on anything clickable. The client's discipline buttons are 90px; ours are 64px, which is generous and leaves room for five in a row.
- Colour-independence: enforced by P5. Every state carries a word. Grade badges show the letter, not just the hue. Deltas show the sign and the direction word, not just the colour.
- Keyboard: the guided flow is fully operable without a pointer. 1-5 pick a discipline, Enter confirms, Escape steps back one card. The turn timeline is a list with roving focus so a Trainer can read it with a screen reader in order.
- Screen readers: the stat band is a table or a list with explicit labels, not a row of divs. Gain bubbles are `aria-live="polite"` so a change is announced without stealing focus.
- The faceted page field and the lattice bleed are decorative CSS and are not exposed to assistive technology.

---

### 11. Open decisions

Surfaced, not resolved. Each needs the owner or a role escalation.

1. **Font dependency.** The rounded mandate is settled (§4.1) and Nunito is recommended, but it is still a new dependency and root C-8 requires the owner's approval. Self-hosting is the expected answer for a tool that must work offline (NFR-1); a Google Fonts `<link>` would break that. The web study came back and did **not** settle it in Nunito's favour: Cygames' own sites run Roboto. The client, which is what we are matching, is rounded. The owner should confirm the tool follows the client and not the marketing site.
2. **Per-trainee accent.** The client tints its HUD to the character (Oguri Cap green, Mejiro McQueen blue; RAW-FINDINGS §7.2). We cannot follow it without a colour column on `umamusume`, which has no PRD requirement. Escalation: Architect, and PRD §6 governs if it becomes a scope question.
3. **Grade derivation. Partly closed by client frames; still not sourced.** §6.7 previously invented a 150-point banding. Ten (value, letter) pairs read off the Legacy Select screenshots replaced it with a 50-point half-step ladder, because the client itself puts 75-96 at `G+` and 104-143 at `F`. That measures exactly **one** boundary, G+ / F at 100; the remaining fourteen are extrapolation. The `[Provisional]` label and the printed boundaries stay until a capture crosses each threshold or the table is exported. Note the same frames also disproved the flat "+16 per ★3" breakthrough figure, showing increments of 4, 16, 18, 20 and 25 over a 1300 base.
4. **Per-grade badge colour. Resolved, with the gold collision accepted under protest.** §6.7 now defines nine badge tokens in both themes, and the white-letter rule that came with the indigo draft is void: measured against the light fills, white text lands between **1.20:1 and 1.44:1**, which is not a near miss but a different planet. `ink-strong` on the same nine fills measures **9.19 to 11.03** in light and **9.00 to 12.84** in dark. Grade S and SS take a stronger gold per the client's convention, which does sit next to `pick` gold; they are separated by surface (a small filled square with a letter, versus a 2px outline on an interactive row), not by hue.
5. **Mood state strings. CLOSED 2026-09-27.** An owner-supplied capture of the client's Mood Effect panel gives all five tier words (`GREAT` / `GOOD` / `NORMAL` / `BAD` / `AWFUL`) and both effect columns, so §3.5's tokens are renamed to them and D-20's mood-specific block is lifted. What remains open is narrower and is a colour question, not a terminology one: the three lower pills' measured hues. Until they are captured from the HUD the values are derived at the anchors' luminance and marked provisional, and the arrow rule (D-259) is what carries the distinction.
6. **Dark mode.** In scope, specified in §3.7, and anchored on the measured raceboard surface. Open question is narrower than the earlier draft of this file assumed: does the theme follow the system preference, default to light, or offer a three-way switch? A local single-Trainer tool can reasonably follow the OS, and that is the recommended default.
7. **Product name. CLOSED elsewhere; this file was stale.** PRD OQ-1 was closed on 2026-09-27 with the name **Trainer Desk**, recorded in root `DESIGN.md` and `PRD.md` §OQ-1. An earlier revision of this item said the question still stood; it does not. This package predates the naming and still carries a descriptive header, which is now the wrong title on a superseded artifact rather than an open question.
8. **Layout convergence. Decided: one spine.** The dashboard and the guided input are a single vertical spine in `screen-a-scenario-v10.html`, and the only thing that varies by scenario is which panels the goal region composes. Four files were retired to `prototypes/superseded/` with reasons in its README. Two specific calls:
   - **Inline preview over side preview column.** The side column separates a choice from its own consequence, and it shipped a real defect where the preview rendered *above* the choices. Adjacency is the rule the guided flow exists for (D-171), and a two-column split only earns its keep above roughly 1,400px.
   - **One anchor, not three.** The v9 mockups gave URA a top-left lead, Unity Cup an off-grid editorial spread and Trackblazer a centred-low stack. Three anchors made three products. Muscle memory across scenarios is worth more than per-scenario art direction, and the scenario difference is already carried by content.
   Still open: Screen C keeps two variants (`screen-c-event-v1-inline.html`, `screen-c-event-v2-preview-column.html`). Same argument applies and it should converge the same way, but it was not part of this decision.
9. **Tint reference surface. Measured, and the spec is imprecise.** §3.6 promises tints at **1.04 to 1.14 against the panel surface**, and the dark set was solved that way: measured against `--raised` it lands **1.09 to 1.11**, tight. The light set measures **1.10 to 1.21 against `--raised`**, because the band cell is painted `--raised` (`#FFFFFF`), not `--panel`. So the documented range was computed against one surface and rendered on another, and G-25 as written fails on measurement for Speed, Stamina and Guts. Either the light tints are re-derived against `--raised` to match the dark discipline, or §3.6 names `--raised` and widens the accepted band. **The reference surface must be named, not implied** — a contrast rule without a stated second colour cannot be checked.
10. **Identity conflict with root `DESIGN.md`. Partly resolved by owner ruling 2026-09-27.** Two owner decisions were taken on the same day and disagreed about visual identity, not about facts:
   - **Root `DESIGN.md` (Trainer Desk):** dark-first, tactical-athletic, system sans with `ui-monospace` numerals, `rounded-md`, 1px borders, "no pastel gradients, no soft shadows, no gacha-wiki look", the rounded display-font proposal **parked**, and the app does not adopt the game's trademarks.
   - **This package:** light-first with a measured dark theme, Nunito or M PLUS Rounded 1s, 14-20px radii, a soft elevation token, and deliberate client resemblance (capsule headers with argyle lattice, torn-paper calendar, hard enamel sheen).
   Two of the five axes now have rulings, and they split — neither document wins the set:
   - **Default theme: resolved this package's way.** Light is the app default; the dark anchors became `html[data-theme='dark']` in `resources/css/app.css`. Root `DESIGN.md` §2.1 carries the reversal and D-100 is normative again.
   - **Typeface: resolved the root's way.** No font dependency is approved (C-8), so `--font-sans` stays the system stack and §4.1's rounded-humanist mandate is **exempted rather than repealed** — recorded in `KNOWN-ISSUES.md`, because G-21 otherwise reads as a pass while failing on our own code.
   - **Still open: radius, elevation, and how much client ornament is permitted.** The motif restorations (lattice, enamel, torn page) shipped on the reasoning that franchise resemblance is the product's identity, which pulls against root's "neutral tool" line; that tension is unresolved and now narrower.
   Consequence for artifacts: `screen-a-scenario-v10.html` validates **structure, scenario composition and label correctness** under §10n. It has always been light, so under the theme ruling it is closer to the target than before — but it is still not a settled visual target while radius, elevation and ornament stay open. The prose that prompted these two rulings is audited in `FRONTEND-SPEC-DIVERGENCE.md`.
11. **The grade ladder above roughly 450 is unvalidated, and `U/G` is uninterpreted.** §6.7's 50-point half-step model fits the low range and is contradicted by Run Completion frames, where the client renders 499 as `C` (the model predicts `C+`), 716 as `B+` (it predicts `S`), and 1245 as a `U/G` badge that is not on the ladder at all. Two questions, one artifact: the real thresholds between 500 and 1300, and what `U/G` denotes. Needed is a sweep of captures crossing each boundary in that band, or the client's grade table if it is ever exported. Until then `CONSTRAINTS.md` D-274 forbids rendering or mapping `U/G`, and the `[Provisional]` marker on the badge strip is load-bearing rather than decorative. This is the one open item that could corrupt a number a Trainer quotes, so it outranks the cosmetic questions above it.

---

### 12. Review log

What a browser pass caught that a static scan could not. Recorded because each of these was invisible in the source and visible only in the rendered result.

| Finding | Why static review missed it | Fix |
|---|---|---|
| Cyan event tag carried white text at **2.98:1** | The hex was a legitimate token; only its pairing was wrong. §3.4 already listed this exact pair as failing, and the component was written anyway | Tag and ribbon moved to `cyan-800`, 5.53:1 |
| **"Skill Points went up by 2." printed twice** per entry | `sp` sat in the delta map *and* had a dedicated branch. Both loops were individually correct | Generic loop now excludes `sp` |
| A Rest turn rendered **". Energy recovered."** with a leading period | `parts.join(". ") + "."` on an empty array is a valid expression producing invalid prose | Sentence built only when parts exist |
| Timeline ordered **13, 10, 11, 12** | `prepend()` reads fine and violates D-46, which this file wrote | `appendChild`, verified 10→14 |
| Variant 2's preview column rendered **above** the choices | The grid row was assigned numerically; only a render shows that a prompt to hover precedes the things to hover | Row corrected, verified geometrically |
| Dark tints spread **1.45 to 1.91** against the cell | Derived at equal HSL lightness, which is not equal relative luminance. Yellow and green carry far more luminance than blue at the same L | Each tint solved by bisection to 1.11 contrast; measured spread now 0.02 |
| Step line stayed **"Step 1 of 3"** after committing | Static copy in the markup, never touched by the handler | Updated on commit |

Two process lessons worth keeping.

**The gate could not see any of these.** The static checks cover lore, terminology, token discipline and text defects in markup, and they passed on all of it. A `node --check` pass and a rendered-DOM assertion pass were added to the gate as a result; the syntax check alone caught a variant whose entire script had failed to parse while its CSS looked perfect.

**Documenting a rule does not implement it.** §3.4 recorded that white on `#009FE1` fails, and §6.5 said to use `cyan-800` for that header. The event tag then shipped the failing pair anyway. A contrast table is only worth what its enforcement is worth, which is why G-5 is now a browser measurement rather than a checklist item.

### 13. Scenario-aware mockup review, v9 round

Four frames generated against `CONSTRAINTS.md` §10n, then read back at full resolution. The audit table lives in `CONSTRAINTS.md` §10o; what belongs here is what it says about this design system.

| What shipped | Why it is a design-system failure | Resolution |
|---|---|---|
| Two frames, two bar-colour schemes — neutral lime in URA, per-stat blue/pink/green/orange/purple in Unity Cup | D-114 bans saturated stat hues, and §3.6's near-invisible tints exist to *suggest* stat identity. The per-stat fill re-imports the withdrawn five-colour scheme, and the two frames stop reading as one product | D-250, G-44 |
| Wit drawn as a graduation cap in three frames and a brain in the fourth | Nothing in the system said which | §6.0b, fixed glyph table |
| Energy drawn as a heart beside Stamina's heart | Two resources, one glyph, adjacent in the same header band | §6.0b; Energy is a battery |
| Every facility row reading "+3 primary / +1 secondary" | A constant where the sourced table says the value varies by flame count and facility. Invented filler in the slot carrying the tool's core number | D-252, G-43 |
| `Suggested` chip in blue | Blue is the loss colour under D-133; a suggestion is not a loss | D-253 |
| "Power facility Lv 4 ← team rank S" | Rank S yields Lv 5. The provenance widget stated the wrong cause, which is the only thing it exists to do | corrected in v9b |
| A CALENDAR nav item on the Trackblazer overlay's dimmed background | The scenario has no race calendar. Removing the panel while leaving its name in the rail says the false thing anyway | D-254, G-45 |

**The load-bearing conclusion.** Not one of these was catchable by `gate.py`. It scans HTML rendered text; the mockups are PNGs, and D-184 already recorded that they cannot be gate-verified. The closest call was a fabricated skill name, **"Endurance Up"** — the gate *does* ban `endurance`, so the same string pasted into a prototype would have failed G-3, but as a picture it reached a human and nowhere else.

That asymmetry is the argument for the next piece of work rather than a criticism of this one: **any string that must be correct belongs in an HTML prototype.** The mockups are art direction — they settle composition, density, colour dose and whether a scenario's strip reads as that scenario. They are not evidence that a label is right, and the four frames in this round should be read as the visual target and nothing more.

### 14. Legacy and Run Completion mockup review

Three frames generated for §6.27 and §6.28, then read back at full resolution. The
documentation is correct; the pictures are not, and nothing automated could tell the
difference. This is the fourth consecutive round in which a rendered PNG contradicted a
sourced number, and it is the argument for the HTML surface, restated with evidence.

| Defect | Correct value | Where it came from |
|---|---|---|
| Class pyramid renders `Gold 550,000` | Gold is **50,000**. `550,000` does not exist in the client. | Invented digit. The threshold column is also shifted, so Star shows 100,000 instead of 160,000 and 160,000 is absent entirely. |
| Stat rows ordered Stamina, Power, Guts, Wit, Speed | Fixed order is Speed, Stamina, Power, Guts, Wit (§6.5). | Order is itself information; a Trainer scans positions, not labels. |
| Mini medals carry garbled micro-text | No text belongs there. | Filler glyphs the generator invented to fill a small area. |

What survived verification, and is worth keeping as the target: the `U/G` problem is rendered
honestly. The Speed row shows a grey badge with a question mark and a dashed `tier
unconfirmed` tag rather than guessing a letter, which is D-274 expressed visually. The
pyramid marks the reached tier with the `KEEP` pointer instead of leaving a bare fan count.
The two Major Win icon kinds are visibly distinct, gold disc for composite titles against the
blue `G1` badge for a single race. `Sprint F` is periwinkle, confirming D-273 against the
earlier draft that grouped F with G. And the footnotes state that rank and rating are recorded
rather than computed, which is the Planner Rule 1 boundary made visible.

**The rule this round establishes about mockups.** A PNG may set composition, density, colour
and whether a layout reads as this product. It may not be the place a number, a label or a
threshold is first written down, because every one of those has to survive a grep, and a
picture cannot be grepped. The pyramid defect is the proof: the correct thresholds were
already in §6.28.4, and the mockup corrupted three of them anyway.

---

### 15. External review pass, claims versus citations (2026-09-27)

A cross-document review of this package raised nineteen findings. Each was checked against the
files before being acted on. Verdicts below are keyed to that review's numbering; **rejected**
means the proposed fix would have made the corpus less accurate, and the underlying defect was
still real.

| # | Verdict | What was verified and done |
|---|---|---|
| 1.1 | Accepted, with the reviewer's premise corrected | "A gate above the maximum possible value cannot exist" was too absolute. Source Conflict Log row 3 is **reconciled, not open**: `hard_caps` is per scenario — 2000 on the twelve older, 2500 on Beyond Dreams, whose cap is `1200 + 900 = 2100`. Row 3's own closing instruction holds: on `[Global]` no cap exceeds 1900. |
| 1.2 | Accepted | White and Scenario Sparks are **export-confirmed**, not "plausibly corroborated": `REFERENCE:585-592` gives 452 / 37 / 34 records, basis `[A]+[A]+[B]`. Only their *rendering* remains uncaptured. |
| 1.3 | Accepted | `REFERENCE:571` records the `[Global]` system name as **Inspiration**, ancestors as **legacies**. Rows added to `CONSTRAINTS.md` §4. The apparent clash with the **Legacy Select** heading is not a clash: the owner ruled that whole set on 2026-09-27 (root `DESIGN.md` §6, `PRODUCT.md`). Residual is only that neither caption is frame-captured, so neither may be styled as the client's own. First draft of this row framed a closed ruling as an open one. |
| 2.1 | Accepted, severity raised | The real damage was not the headline table but the schema argument at `SCENARIO-DIFFERENCES.md:196`, which rejected `race_entries` because Trackblazer "has no race goals" — objective 1 is the Debut race and all Grade Points come from racing. Correct argument now: no *prescribed fixture list*, per-race rows still required. |
| 2.2 | Accepted | "permanent for the run" restored to the shop-level row (`05:99`, 150 coins). |
| 2.3 | Accepted | Race Fatigue table marked single-source and provisional; `90%+` called out as a **lower bound**, so printing `90%` would state a figure no source gives. |
| 2.4 | Partly | The three-of-four caveat already governs the matrix, and §10n already makes the fourth column the architectural test. Per-rule stamps on every derived rule would be noise; the weight went where it binds — D-230's new sourcing bar and the §10n column note. |
| 3.1 | Partly | `SCREENSHOT-MANIFEST.md:41` already stated that a three-way comparison is impossible on this corpus. The coverage *cell* still read "Present", which is what a skimmer takes; now "Minimal — two frames, not a coverage basis", and it says what the two frames do and do not evidence. |
| 3.2 | Accepted | Confirmed: the six activity names are all Unity Cup frames, so the cross-scenario inference was unevidenced. Found in **four** copies (`manifest:64`, `D-187`, `DESIGN §6.4b`, `§6.22`), not the one file cited. Rule kept, reason withdrawn — `D-287`. |
| 4.1 | **Rejected as proposed**, accepted as diagnosis | The reviewer's fix was to downgrade "always halved" to "reduced effect, stale source". That would have replaced a `[Global]` post-rework claim with a stale JP one. What was actually wrong: **`D-211` misattributed its own quotation** to `01-ura-finale.md`, which contains neither *halved* nor *1200*; the source is Game8's `[Global]` EN pages per `ADR-0002` amendment 1. `Fully Charged` was also credited to URA when it is an Our Grand Concert mechanic. Fixed at `D-211` and at `REFERENCE §1.3.4`, where the training-gain and race-effect axes were conflated. |
| 4.2 | Accepted, and found shipped | `+0 breakthrough` asserts a game fact the tool cannot observe. Disclosure line corrected, with a three-state split (entered zero / not tracked / tool limitation). **The same string is live in `resources/views/components/stat-band.blade.php:153`** and the converged prototype — see the open decision below. |
| 4.3 | Partly | The near-identical hues were already documented at §6.17 item 1; the missing piece was that this makes **pill size a correctness constraint**. Added, deliberately without a numeric floor, since inventing one would repeat the failure being fixed. |
| 5.1 | Accepted, with evidence correction | `turns-left` is *not* unsourced — §6.6 reads the remaining count and its contrast ratios off frame `194819`. The hidden defect was arithmetic: `total − current` has no total, so the value is entered, per D-270's pattern, and period-remaining must not be conflated with career-remaining. |
| 5.2 | Accepted | D-211 now states its verification status against D-212's data-level confirmation: threshold corroborated by the export, halving prose-only, and the source page undated. |
| 5.3 | Accepted | D-230's opening clause was a general principle wearing a specific rule's clothes. Now: client data **or** two independent sources, with Race Fatigue held as a provisional exception. |
| 5.4 | Accepted, and caught a second error | The gates table did not separate scripted from manual. Corrected against `gate.py`'s own PASS banner — my first pass read grep fragments and **inverted the two lists**, which is the defect this whole section is about. |
| 5.5 | Accepted | `§4` presented `Pal` as a required label while D-278 says it was never captured. Row now carries `[uncaptured]` plus a note that keeps the two readable in isolation. |
| 6.1 | Accepted as an axis split | Not a wording alignment: two different mechanisms, now separated in `REFERENCE §1.3.4` so a UI sentence cannot quote one as the other. |
| 6.2 | Accepted as a standing gap | Our Grand Concert remains the least-documented Global scenario; §10n treats it as the architectural test and D-287 bars using the three known scenarios as proof of a cross-scenario pattern. |
| 6.3 | Accepted | Correction propagation is now D-286 and G-60; the misattribution sweep in this round is that gate applied by hand. |

**One new rule came out of the round.** `CONSTRAINTS.md` **D-287**: a correct rule with a wrong
reason still fails review, and it is the kind that survives every check, because review reads the
requirement and skims the citation. Two of this package's rules were of that shape, and both had
already been copied outward.


---

## CONSTRAINTS.md

docs/design-research/CONSTRAINTS.md: design contract


**Addendum, not replacement.** The repository root's `CONSTRAINTS.md` is the quality bar and this file does not weaken it. Nothing here overrides C-1 through C-8 or the Floor section of the root file. Where this file adds a gate, it is additive: a change must pass both.

Scope: the user-facing layer of Umamusume Trainer Companion. Applies to Blade views, components, CSS, client JS, exported CSV/JSON headers, seed and fixture strings, UI copy, and design artifacts such as mockups and prototypes.

Status: contract for review. No production UI code exists yet, so nothing here has been violated yet either.

---

### 1. Token contract

**D-1. The `@theme` block is law.** Every colour, radius, shadow, and type step used in a view must resolve to a token defined in `DESIGN.md` §3.5. Raw hex literals, arbitrary values (`bg-[#7FCC09]`), and magic numbers (`rounded-[13px]`) are review failures.

**D-2. One source of truth per value.** A token is defined once in the theme and referenced everywhere else. A colour appearing in two files is a colour that will drift.

**D-3. Chrome steps never carry text.** The bright step (500) of any hue is fill, border, ring, and ornament. Any surface carrying text uses a step from §3.4's approved pair table. This is the single most likely violation in this codebase, because the game's own chrome does not obey it.

**D-4. No Tailwind default palette in semantic roles.** `green-500`, `blue-500`, `red-500` and friends must not stand in for a design token. Tailwind's `green-500` is `#22C55E`; the action green is `#7FCC09`. The difference is the product's identity.

**D-5. Radius nests.** Inner radius equals outer radius minus the padding between them. A 14px card inside a 14px panel is a defect.

**D-6. Shadows carry the cool cast.** No neutral-grey shadow, no `backdrop-filter`, no glassmorphism, no neumorphism, no inner shadow. These do not appear anywhere in the source corpus (`RAW-FINDINGS.md` §3.1, §3.5).

**D-7. Circles mean "pick one of five."** Fully round interactive elements are reserved for the discipline affordance and avatars. Rounding anything else dissolves the meaning.

---

### 2. Contrast contract

**D-10. Every text/background pair must pass WCAG 2.1 AA** at its size: 4.5:1 for text under 18.66px bold or 24px regular, 3:1 above. `DESIGN.md` §3.4 lists the approved pairs with computed ratios. A new pair needs its ratio computed and recorded before it ships, not after.

**D-11. The client's own failing chrome is not a precedent.** White on `#7FCC09` measures 1.99:1. Reproducing it because "the game does it" is a violation, not fidelity. Where fidelity and legibility conflict, legibility wins and the departure is recorded in `DESIGN.md`.

**D-12. State is never colour-only.** Every coloured state carries a word, a glyph, or a shape difference. Applies to mood pills, run status, skill acquisition status, validation errors, and grade badges. This is both `DESIGN.md` P5 and a functional requirement: a Trainer who cannot separate the hues must still read the state.

**D-13. Focus visible always.** `outline: none` without a replacement ring is a review failure. The ring is 3px `green` at 35% alpha.

**D-14. The faceted page field and the lattice bleed are decorative** and must not be exposed to assistive technology or carry information.

---

### 3. Lore integrity (NFR-6)

Non-negotiable, blocking, and owned by the Lore Guardian role in `AGENTS.md`. A single confirmed violation fails the change. The grep proposes; the Guardian decides.

This section names the banned patterns in order to forbid them, which is the same practice the root `CONSTRAINTS.md` C-4 list and `CLAUDE.md` Banned Patterns already use. It adds no new vocabulary choice; it adds a dictionary. `make lore` will report hits here, and the Guardian's ruling on these lines is the same as on the root file's own list.


#### 3.1 Banned vocabulary for characters

Grep case-insensitively across all UI text, identifiers, seed data, export headers, alt text, and mockup copy:

| Banned | Why | Required instead |
|---|---|---|
| `horse`, `horses` | the core violation | Umamusume | <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.1 -->
| `sire`, `dam` | parental animal terms | inheritance parent, parent umamusume | <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.1 -->
| `mare`, `stallion`, `filly`, `colt`, `gelding` | sex-specific animal terms | umamusume, trainee | <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.1 -->
| `foal` | offspring term | trainee umamusume | <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.1 -->
| `equine`, `pony`, `thoroughbred` | animal framing | umamusume | <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.1 -->
| `stable` as a noun for a character container | animal framing | roster, catalog, collection | <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.1 -->
| `breeding`, `pairing`, `bloodline`, `pedigree`, `lineage` of characters | the cut PRD §6.3 system | inheritance (two parent references only) | <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.1 -->
| `🏇` and any horse/riding emoji | costume framing | none | <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.1 -->
| `hoof`, `mane`, `tail`, `withers`, `muzzle` | body-part animal framing | avoid; describe the garment or accessory if needed | <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.1 -->
| `jockey`, `rider`, `saddle`, `tack`, `reins`, `bit` | the human-animal control frame | Trainer (which is us, and means something different here) |
| `paddock`, `herd`, `flock`, `pack` | collective animal nouns | roster, list, catalog |
| "your horse", "your mount", "the animal", "the girl and her horse" | framing | your trainee, the umamusume | <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.1 -->

#### 3.2 Context-allowed senses (do not "fix" these)

The grep will hit these. They are not violations, and the Guardian's ruling stands over the tool's count:

- `dam` inside `damaged`, `demand`, `command` <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.2 -->
- `sire` inside `desired`, `surprise`, and the Umamusume name `Red Desire` <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.2 -->
- `stable` as an adjective: "stable growth", "keep the build stable", "a stable control identifier" (`tests/Feature/ReviewFormAccessibilityTest.php:39`, ruled 2026-09-28). The ban is on `stable` as a noun naming a character container; an adjective describing anything is the allowed sense. <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.2 -->
- `mare` inside `nightmare` <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.2 -->
- `tail` inside `detail`, `retail`, `curtail` <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.2 -->
- `account` in the verb idiom "account for": "the list the template has to account for" (`tests/Feature/ReviewQueueTest.php:62`, ruled 2026-09-28). The bill and login senses are what the ban exists for, and `PRD.md` NFR-1 declares this tool has no account and no auth surface, so neither can appear as product language; "account for" means "cover", which is ordinary English in a code comment.
- `account` inside Laravel's own shipped queue defaults: `your-account-id`, the AWS SQS env fallback at `config/queue.php:60` (ruled 2026-09-28). `account` and `login` sit in the `lore-code` word list to catch an auth surface being built, which is a scope question for the Planner Domain Specialist and Architect, not a lore question about a framework stub nobody wrote copy for.
- Japanese source strings and quoted official notices: source data, never a violation in itself
- `docs/PRE-MORTEM.md` legacy quotations, exempt once each under root C-4

#### 3.3 Required forms

- The race name is **Umamusume**: a humanoid race. Capital `U` at sentence start or as the proper race name; lowercase `umamusume` mid-sentence.
- **Singular and plural are identical.** "one umamusume, three umamusume." Never "umamusumes", never "umamusume's" for a plural possessive.
- Characters are referred to as **Umamusume** or, in the training context, **Trainee Umamusume**.
- A character with a finished career is a **Veteran Umamusume**. Not "graduated", not "retired mount"; the run status enum case `Retired` is fine because it names the run, not the character.
- The human user is the **Trainer**.
- Sentence case in all UI copy. No decorative emoji in labels, headings, or buttons.

---

### 4. Official terminology (Global client strings)

UI labels must use the Global English client's words, not the JP client's and not a wiki's paraphrase. Source: `docs/UMAMUSUME_REFERENCE.md` §6 (server terminology map), §1.1, §1.3.

| Concept | Required label | Banned alternatives |
|---|---|---|
| The five stats | Speed, Stamina, Power, Guts, Wit | Intelligence (the export key for 賢さ, not the client label) |
| Skill currency | Skill Points, abbreviated SP | Skill Pt, Skill Pts as a UI label |
| Running styles | Front Runner, Pace Chaser, Late Surger, End Closer | Runner, Leader, Betweener, Tracker, Chaser |
| Style abbreviations | Front, Pace, Late, End (aptitude table only) | the four full names inside a dense aptitude grid |
| Distances | Sprint, Mile, Medium, Long | Short, Middle, Staying |
| Surfaces | Turf, Dirt | Grass, Sand |
| Support card types | Speed, Stamina, Power, Guts, Wit, `Pal` **[uncaptured]**, Group | Friend (for 友人) |
| Gacha | Scouts | Gacha, Pickup, Banner |
| Pull currency | Carats | Jewels, Gems |
| Character being trained | Trainee Umamusume | Trainee Uma Musume, Uma Musume being trained |
| Finished character | Veteran Umamusume | Hall of Fame, graduated |
| Inheritance unit | Spark | Factor (JP-side 因子 wording) |
| Inheritance **system** | Inspiration | Inheritance as the English label (that is the JP 継承 word, not the `[Global]` one) |
| Ancestors picked for a run | Legacies | Parents, grandparents, bloodline (D-267 bars these in copy) | <!-- lore-ignore-line class=1 cite=D-267 -->
| Energy | Energy | Stamina for the gauge (collides with the stat) |
| Mood | Mood, with the five state words shown | Motivation (the JP guide gloss), Condition |
| Scenario names | Ura Finale, Unity Cup, Brighter Together Our Grand Concert; Trackblazer or Twinkle Star Climax for the third | Make a new track!!, Climax bare |
| Rest action | Rest | Break, Vacation |
| Bond gauge | Bond | Friendship gauge, trust |
| Friendship training | Friendship Training, Friendship Bonus | Rainbow training (a guide nickname) |
| Hint discount | Hint Lvl N, NN% OFF | Hint level N discount |
| Skill acquisition states | Suggested, Acquired, Skipped (the `SkillAcquisition` enum) | Planned/Used/Ignored, Proposed |
| Run states | Active, Completed, Retired (the `RunStatus` enum) | Ongoing/Finished/Archived |

**What `[uncaptured]` means in this table.** The labels in the middle column are not equally evidenced. The five discipline names are client strings read from captures; **`Pal` is not** — it is Game8 English's word for 友人, and the client's own label for the sixth type has never been read off a frame (D-278). "Do not ship it as though the client said it" is D-278's ruling, and this table's job is to keep a reader of one row from concluding what a reader of both rows knows. Same status applies to the running-style abbreviations' *rendering*, not their words.

**Naming here is already ruled; the residual is narrower than it first looks.** `UMAMUSUME_REFERENCE.md:571` records `[Global]` localising the **system** as *Inspiration*, with *legacies* for the chosen ancestors and *Sparks* for the traits, and the owner confirmed that set on 2026-09-27 in root `DESIGN.md` §6 and `PRODUCT.md`, naming the screen itself **Legacy Select** — which §6.27 and D-260 use throughout. The only open edge is evidentiary, not decisional: **neither "Legacy Select" nor "Inspiration" has been read off a captured frame**, so both are legitimate as *this tool's* vocabulary and neither may be styled as the client's own caption (D-20). Check for an existing ruling before escalating a naming question as though it were new — this note first framed a closed decision as an open one.

**D-20. Never invent a client string.** If a label is needed and no Global string is on record, use a plain descriptive phrase and record the gap. `UMAMUSUME_REFERENCE.md` marks its own unverified items with `❌ UNVERIFIED`; a design artifact must not silently promote one into UI copy. This rule is general and stands. Its mood-tier instance is **resolved, not relaxed**: the client's own Mood Effect panel (owner-supplied capture, 2026-09-27) prints `GREAT` / `GOOD` / `NORMAL` / `BAD` / `AWFUL`, so those five are evidenced client strings and §6.17 of `DESIGN.md` now uses them as UI copy. The strings this rule still blocks are every label the corpus has not captured from the client itself, the outing action's button text among them.

**D-21. Do not key on fan-translation strings.** Some community English names carry banned vocabulary that the official Global label does not (the reference doc's Rainbow Cleat case). Official Global text wins.

---

### 5. Data-model binding

The UI is a view over the schema, and the schema is fixed by `ARCHITECTURE.md` §3. A screen that needs a field the schema does not have is a scope question for the Architect, not a reason to add a column.

**D-30. Render only what exists.** The permitted surface is: `TrainingRun` (umamusume, scenario, status, two inheritance parents, notes, `character_card_id`), `TurnEntry` (turn, Speed, Stamina, Power, Guts, Wit, SP, condition), `run_skills` (status, turn acquired), `Skill` (name, name_ja, sp_cost, type, is_unique), `Umamusume` (name, name_ja, release_status, debut dates, is_manual, aliases), `CharacterCard` (`character_cards`: card_id, umamusume_id, title, rarity, global_release_date, is_debut_form, unconfirmed, and its inline provenance set `source_url`, `snapshot_path`, `fetched_at`, `source_timezone` plus the card's own `is_manual`, per `ADR-0003` Amendment R3 as plan Amendment A1 adopted it on 2026-09-29), `data_sources` (provenance at character grain: `umamusume_id`-scoped, and not where a card's provenance lives), `match_candidates`. `CharacterCard` and `TrainingRun`'s `character_card_id` joined this list on 2026-09-29 under `docs/adr/0008-character-card-catalog-layer.md`, after the owner ruled on the scope question this section's preamble raises: the entry is the record of a ruling, not a relaxation, and the rule stands for every column that has not been asked and answered the same way. `character_cards` was authorized and not migrated; `2026_09_29_120000` and `2026_09_29_120100` landed it on 2026-09-29 with `CharacterCard`, `CardRarity` and `Umamusume::cards()`, so the card columns listed above are renderable today. `TrainingRun`'s `character_card_id` was authorized and not migrated; `2026_09_29_120200` landed it on 2026-09-29 as a nullable foreign key to `character_cards.id`, with `TrainingRun::characterCard()`, a fillable `character_card_id` and a same-trainee `exists` rule on `StoreTrainingRunRequest`, so a screen may now read the form a run started on.

**Amended 2026-10-02 (PLAN-UI-UX-2026-10-02 Task 3.1). Two entries above widen, and each widening is a catch-up on a record that already exists elsewhere.**

`CharacterCard`'s entry gains `skills_innate` and `skills_unique`, permitted for one use: **grouping a card's own skills**. The two lists decide which band a skill sits in and nothing else, so a skill's own text renders through the `Skill` entry above and its fields, and no export id reaches a screen.

`Umamusume`'s entry gains the ten `aptitude_*` columns (`aptitude_turf`, `aptitude_dirt`, `aptitude_sprint`, `aptitude_mile`, `aptitude_medium`, `aptitude_long`, `aptitude_front_runner`, `aptitude_pace_chaser`, `aptitude_late_surger`, `aptitude_end_closer`), permitted for one use: **the trainee detail page's aptitude section**, section 2 of the eight-section page as root `DESIGN.md` §4.2 fixes it, each cell letter and word.

Neither clause authorises a schema change, a migration, or a new surface: both name columns the tables already carry (`2026_09_30_073029_add_skill_lists_to_character_cards_table` added the two lists; `2026_09_27_090000_add_aptitude_grades_to_umamusume_table` added the ten grades). Nothing here reaches a skill's contents, the awakening and event arrays, which stay unrecorded, or aptitude grades at card grain, which stay out (`ADR-0012`). The amendment is a catch-up rather than a new ruling: `ADR-0012`'s Decision 1 row already counts the card at fourteen columns ("fourteen now that `dd90330` added `skills_innate` and `skills_unique`"), and the aptitude letters have been parsed, populated and rendered since `555b0cb` shipped `x-aptitude-grid`. The drafted source for this text is `PLANS-AND-BRIEFS.md` §"d-30-amendment-draft-2026-10-01.md" §2, landed here with its stale `docs/design-research/CONSTRAINTS.md` citation corrected to this file, which is where §5 lives since the corpus consolidation.

**D-31. The 0..1200 validation bound is a defect, and the UI must stop hiding it.** `PRD.md` FR-C-2 and `CLAUDE.md` Planner Rule 6 fix stat input at 0..1200, but every live scenario caps higher: Ura Finale 1400 across the board, Unity Cup Wit 1800, Trackblazer Stamina 1900, Our Grand Concert Speed 1600 (`UMAMUSUME_REFERENCE.md` §1.3.4). **A real run cannot be recorded.** P0 US-3 therefore fails on legitimate gameplay data, and this is a product blocker, not a display question.

An earlier revision of this rule told the UI to "show `/1200`" and never imply a wider ceiling. That was wrong: it dressed a schema limitation up as a design decision and would have made the tool look internally consistent while being unable to hold the thing it exists to track. Withdrawn.

Current requirements, pending `docs/adr/0002`:
- The stat bar scales to the **scenario base cap for that stat**, per D-160, not to a global constant.
- The 1200 validation limit renders as a visible marker on the bar, labelled as the tool's current bound, so the gap between what the game allows and what this app accepts is explicit rather than silent.
- A value above 1200 must not be rejected with a message implying the game caps at 1200. Until the bound is raised, the error names the limitation as the tool's own.

**Erratum 2026-10-02 (PLAN-UI-UX-2026-10-02 Task 3.2). The bound this clause describes no longer exists.** `ADR-0015` (`docs/adr/0015-per-scenario-stat-ceiling.md`, landed at `8bda7db`) replaced the flat 0..1200 validation bound with the run's own per-stat scenario ceiling, and the clause above is kept verbatim as the record of why the flat bound had to go. Two dated pointers inside it are superseded with the bound: the "pending `docs/adr/0002`" line resolved when that ADR landed, and the "until the bound is raised" instruction describes a limit the request classes no longer apply. The architecture documents carried the same stale number and were corrected at `79d5f6f` with dated errata citing `ADR-0015` and `8bda7db` (KI-48's subject); this erratum is the design-corpus half of the same correction, landed in the same commit as the D-30 amendment above so the two adjacent clauses cannot disagree on disk.

**D-32. Turn numbers start at 1 and are unique per run.** The UI must reject a duplicate at the input, not silently renumber, because PRD US-3 makes the rejection an acceptance criterion.

**D-33. Every engine-sourced fact shows provenance.** Source URL and fetched date at `meta` weight on any catalog read (FR-A-4, NFR-2). Trainer-entered run data shows no provenance, because the Trainer is the source, and adding a fake one would be a lie.

**D-34. Timezone correctness is visible.** Dates display through `config('uma.display_timezone')`; date-only values render identically in every timezone (US-7). Never hardcode a locale. Any date-arithmetic change needs a `freezeTime()` test.

**D-35. Deterministic and explainable.** Every derived number on screen is a pure function of entered turns (Planner Rules 4 and 5). No estimated outcomes, no "recommended" figures, no confidence scores the Trainer did not produce. If a value cannot be traced to turns on screen, it does not render.

**D-36. No simulation, prediction, or snapshot surface.** PRD §6.11 and §6.12 are absolute. No race prediction, no aptitude grade input, no race-day snapshot, no localStorage-authored run, no trainee image upload (§6.13). A mockup that shows one of these is a scope violation even as a picture, because it sets an expectation the PRD forbids.

**D-37. No auth, no accounts, no multi-user affordance.** No avatar menu, no "sign in", no workspace switcher, no share link. PRD §6.1.

---

### 6. UX architecture: Trainer's Dashboard (Screen A)

The persistent layout contract.

**D-40. Two-region desktop frame.** The frame has two regions with different lifetimes: a **state region** holding run identity, turn chip, resource strip and persistent stat band, and a **work region** holding the turn timeline and the guided step. The state region never scrolls away; the work region scrolls and groups by phase. That split is the rule, and it is the spec's own words: `docs/UX Behavior Specification - Umamusume Trainer Companion.md:136-141` names the two regions, lists what each contains, and gives them exactly those two behaviours ("Persistent, never collapses" / "Scrollable, grouped by phase"). This also mirrors the client's own two-window composition (`RAW-FINDINGS.md` §3.8), and PRD §2 fixes the tool to a desktop browser.

**Axis is not the rule (amended 2026-09-28, R27).** The spec's table draws the regions left and right; the built frame stacks them, state region pinned above the work region at `lg:` and up. The reason is measured, not tasted: the stat band is six columns, and six columns beside a timeline inside `main`'s 64rem render as unreadable slivers, so a left/right frame satisfies the drawing and breaks the legibility the drawing exists to serve. `slice-5-2026-09-28.md` §3 records the persistence measurement that the rule actually gates on (the state region stays on screen through a full-page scroll). A left/right implementation is compliant only if it keeps the band's six columns readable at the widths this tool targets; nothing in this rule prefers one axis over the other.

Below 1024px the two regions stack on the narrow axis. The brief does not require mobile and no mobile-first compromise is accepted in exchange for desktop density.

**D-41. The stat band is persistent.** It does not scroll away and it is never collapsed. A Trainer reading turn 24 needs the totals at the same moment they read the entry, exactly as the client keeps the band on screen during training.

**D-42. The turn chip is the orientation anchor.** It is present at all times, shows turn number and phase, and is the first thing in the reading order.

**D-43. The timeline groups by phase and collapses.** Entries are `DESIGN.md` §6.9 cards; groups use the collapsible phase bar. Default state: the current phase expanded, earlier phases collapsed. A 24-turn run must fit one screen of scrolling, not four.

**D-44. Planned versus actual is one view, not two.** Skill rows carry the `Suggested` / `Acquired` / `Skipped` status inline with the turn it resolved (US-4). Splitting plan and outcome across tabs destroys the comparison the feature exists to enable.

**D-45. Every data region on the dashboard implements all four states** (root C-7): empty, loading, populated, error. An empty run is the first screen a Trainer ever sees, so it is a designed screen, not a gap.

**D-46. The log is append-last and never re-sorts under the reader.** A new turn appears at the end and the view scrolls to it once.

---

### 7. UX architecture: Guided Turn Input (Screen B)

**D-50. Decision cards, not a form.** A turn is logged as a sequence of single-question steps in the game's voice, using the `DESIGN.md` §6.10 choice-card pattern. A wall of six numeric inputs is the failure mode this whole design exists to avoid.

**D-51. Preview before commit, always.** Each option shows its consequence before acceptance, and acceptance is a separate explicit action (P2, `RAW-FINDINGS.md` §5.1). Selecting must never write.

**D-52. One question per step, in the client's decision order.** The sequence is: which training this turn, then the outcome numbers it produced, then any skill event, then a note. Not the schema's column order.

**D-53. The raw-entry escape hatch exists and is secondary.** A Trainer correcting a mistyped turn or importing from a spreadsheet needs direct field access. It is reachable, it is not the default, and it uses the §6.14 field pattern.

**D-54. The step's question text uses official terminology only** (D-20). "Which training are you focusing on this turn?" is acceptable plain English. A step prompt must not introduce a concept the client does not have.

**D-55. Keyboard-first.** 1-5 select a discipline, Enter confirms a step, Escape steps back. The full flow is completable without a pointer.

**D-56. Validation errors return to the step that caused them,** with the offending option re-presented, not to the top of the flow with a generic banner. Range errors cite the bound: "Speed must be between 0 and 1200."

**D-57. Two layout variants must be evaluated before one is chosen.** The brief requires it and the choice is load-bearing for every later screen. Variants differ in where the preview lives (beneath the option versus in a persistent side panel), which is the real design tension at desktop width.

---

### 8. UX architecture: Run List (Screen C) and Skill Search (Screen D)

**D-60. Run list is cards, not a table.** The turn-depth chip wants to be an object, and a row would flatten it. Each card carries: trainee name, scenario, status pill, turn depth, last three stat deltas, and the Suggested/Acquired/Skipped tally.

**D-61. Sort and filter are header-weight controls** at `label` size. A toolbar that outshouts the data inverts P3.

**D-62. Skill search matches on `match_key`,** never on display strings (FR-D-2, root Banned Patterns: no matching on display strings). A Japanese query may return an English row.

**D-63. A search result shows which field matched.** Skill name, Japanese name, or alias: the Trainer needs to know whether they found the skill or an alias of it.

**D-64. Search results are `DESIGN.md` §6.11 skill rows.** The client's skill row is already a search result; inventing a second presentation for the same object is ornament, not design.

**D-65. Empty search and no-results are different states.** The first invites a query; the second says the skill is not in the catalog and offers the review-queue path (US-5), because a missing skill is a data-fetch gap, not a user error.

**D-66. Autocomplete is debounced and cancellable,** and never fires a network request. Everything here is local (NFR-1); a remote call in a search box would be both a bug and a privacy violation.

---

### 9. Anti-slop rules for this product

Beyond the general `antislop` / `antislop-ui` gates, these are the specific ways this UI would go wrong:

**D-70. No purple-blue gradient.** It is the tell of an AI-generated dashboard and it appears nowhere in the corpus.
**D-71. No glassmorphism, no blurred panels, no neon-on-charcoal.** Not present in the source material (`RAW-FINDINGS.md` §3.1).
**D-72. No Inter, no default system-ui as the primary voice.** The client's letterforms are rounded (`DESIGN.md` §4.1).
**D-73. No emoji as icons.** No 🏇, no 🐎, no ✨ sprinkled through labels. <!-- lore-ignore-line class=1 cite=D-73 -->
**D-74. No racetrack clip-art, no horseshoe motifs, no silhouette stock imagery.** Costume, and partly a lore violation. <!-- lore-ignore-line class=1 cite=D-74 -->
**D-75. Ornament budget: at most two motifs per visual region** (`DESIGN.md` §7). The client's density works against a character model we do not have.
**D-76. No fake data presented as real.** Any trainee, skill, or turn appearing in a mockup or prototype is labelled sample data where the reader can see it. Two further requirements:
- **Use real catalog names, not invented ones.** `Rice Shower`, `Oguri Cap`, `Mejiro McQueen`, `Special Week` and the skill names in `UMAMUSUME_REFERENCE.md` are real Global strings. Inventing a trainee named "Star Blazer" puts fabricated data into the catalog's shape and reads as filler.
- **Never invent a stat value that implies a mechanic.** A prototype may show `Speed 302/1200`; it may not show a predicted final stat, an aptitude grade it did not derive, or a win rate. D-35 and D-36.

**D-77. No lorem ipsum in a delivered artifact.** Placeholder copy is a review failure.

**D-78. Do not design the cut features.** No support-card manager, no event calendar, no race planner, no breeding view. PRD §6. <!-- lore-ignore-line class=1 cite=D-78 -->

**D-79. No AI copy voice.** No "unleash", "seamless", "effortless", "level up your game", no exclamation-mark CTAs, no em dash in prose (`CLAUDE.md` Banned Patterns).

**D-80. Every screen earns its elements.** A stat, chip, or card that does not change what a Trainer knows comes out.

**D-81. A prototype control either works or says it does not.** A button that does nothing, a tab that will not switch, or a filter with no effect is the mockup-wearing-a-product tell. In a visual prototype, either wire the interaction, or mark the region visibly as static. An inert control that looks live is a defect; an inert control that says "static prototype" is honest.

**D-82. Colour dose caps are a review item.** `DESIGN.md` §7.1 caps saturated hues per screen region at 2 plus the standing `green` chrome, `crimson` at one element per screen, `gold` at one selection, and glow at two elements. A screen that fails the squint test, where several hues compete, is fixed by making one of them neutral, not by adding spacing.

**D-83. No decorative left stripe.** A coloured left edge must carry information (`is_unique`, active, warning) and name it. `DESIGN.md` §6.11.

**D-84. No invented iconography family.** The client's icons are soft, weighty, miniature renders. A hairline stroke set (Lucide or a clone) placed beside them reads as a different product. Whatever set is chosen is a decision recorded in `DESIGN.md`, not an import. No emoji, ever (D-73).


---

### 10. Motion contract

**D-90. Nothing animates that is not responding to the Trainer.** No idle motion, no ambient drift, no entrance animation on page load.
**D-91. No transition longer than 240ms.**
**D-92. `prefers-reduced-motion: reduce` is supported** and collapses transitions to a 1ms crossfade, including the stat count-up.
**D-93. Motion never carries information alone.** A state change must be legible with animation disabled.

---

### 10b. Data visualisation

**D-94. No chart without a written question.** The question goes in the title. "Stat progression" is not a question; "how did Speed reach 302?" is. If a sentence answers the question better, write the sentence (`DESIGN.md` §8.1).

**D-95. The default progression view is the turn list, not a line chart.** A Trainer's question about a run is which turn caused a change, and a list of per-turn deltas answers that directly while a six-series line chart does not. A chart is an addition, never a replacement.

**D-96. Chart segments and series must clear 3:1 against each other,** and every series is labelled at its line end rather than in a colour-only legend (D-12, `DESIGN.md` §3.6).

**D-97. No delta without a named comparison.** "+14" must say what it is relative to: the previous turn, or turn 1. An unlabelled trend is an invented metric.

---

### 10c. Theming

**D-100. Light is the default theme; dark is a supported theme.** The client is a high-key light interface, so light is what a Trainer sees first. Dark exists because the owner asked for it and because the tool is read for long sessions on a desktop.

**D-101. Dark is a token override, not a fork.** It re-declares the same custom property names (`DESIGN.md` §3.7). A component rule that branches on the theme with `@apply`-level duplication or a `dark:` utility scattered per-element is a defect: if the theme block is complete, no component should need to know which theme is active. The exceptions are the three named in §3.7 (page texture, shadow, ink warmth).

**D-102. Dark anchors come from the raceboard, not from a default palette.** `#121013`, `#24262A`, `#F5B73C`, `#0170D7`, `#FF1618` and `#AAABB5` are measured from `Screenshot 2026-07-18 000919.png`. A dark surface introduced without a measurement or a stated derivation is a review failure. Charcoal-plus-neon and blue-black are specifically not acceptable defaults here.

**D-103. Both themes pass the contrast contract independently.** D-10 applies per theme. A pair that passes in light and fails in dark is a failure, and the ratio must be recorded for whichever theme it belongs to. `DESIGN.md` §3.7 carries the dark table.

**D-104. Theme follows the system, and the preference lives in SQLite, not the browser.** Amended 2026-09-27 after the light-first ruling; **the original justification for `localStorage` was wrong.** It read "a local single-user tool has no server to store a preference on" — but this tool has a database, and `PRD.md` §6.12 cuts browser-side storage precisely because repo #4's localStorage-vs-account split made the browser a second source of truth. Owner ruling: preferences are persisted in SQLite.

Resolution order, first paint included: **stored preference → `prefers-color-scheme` → light**, where light is the *base palette* rather than a forced default. "Light-first" therefore describes which set the tokens hold at `:root`, not that a dark-OS Trainer is served light. The mechanism is an inline script in the document head — a bundled module runs after first paint and flashes the wrong theme (G-20). Once a preference row exists the Blade shell renders `data-theme` server-side and the script only covers the no-preference case.

Verified in a browser on 2026-09-27 with the OS emulated both ways: light yields no `data-theme` and `--color-page` #F2F1F8; dark yields `data-theme="dark"` and #0D0C0F with ink #ECEAF2, on the real layout rather than the review page.

**One blocker before the storage half can be built.** `AGENTS.md` requires every new table or column to cite a PRD requirement. **US-7** covers the display timezone; **nothing in PRD §2 covers a theme preference or the failure-estimate toggle** that `ADR-0001`'s off-by-default implies. So a `preferences` table needs a PRD line first — recorded as an Architect escalation for the owner, not solved by inventing a key in `localStorage`, which would put the app back into the §6.12 failure this ruling exists to close.

**D-105. Never ship a component that only works in one theme.** Every state in §6.15 and every component in §6 must be reviewed in both. A toggle in the prototype header is the review affordance for this.



### 10d. Game-native fidelity rules

These five came out of a review of the first mockup set. Each one is a place where a competent web default produced something that reads as a clean web app rather than as this game.

**D-110. Rounded humanist sans is mandatory; neutral and geometric grotesques are banned.**
Banned as the primary voice: Inter, Roboto, Helvetica, Arial, Geist, Space Grotesk, and `system-ui` / `-apple-system` when they carry the running text. `system-ui` is banned because it is not one font: a build can pass review on the machine it was written on and read as a generic web app on the next. `DESIGN.md` §4.1 records the measured weight coverage of every candidate and the recommended stack.
- Weight coverage is a hard gate, not a preference: the §4.2 scale needs 500, 600, 700 and 800. **Varela Round serves 400 only and Quicksand tops out at 700**, so neither can carry the scale and both are rejected on that basis, not on taste.
- Japanese coverage must resolve inside the declared stack so `name` and `name_ja` share a baseline and x-height.
- Known counter-evidence, recorded so nobody rediscovers it: Cygames' own web properties use Roboto, not a rounded face. The tool follows the **client** deliberately. See `RAW-FINDINGS.md` §6.

**D-111. Icons are filled and chunky. Thin line icons are banned.**
Solid glyphs, or strokes no thinner than 2.4px at a 24px box, with rounded caps, rounded joins, and 1.5-2px radius on the glyph body. The test: reduce to 20px and solid-black; if it survives only as an outline, it is wrong. Banned sets include Lucide, Feather and Heroicons outline, and no hairline library may be imported wholesale. Emoji are banned as icons (D-73, D-84). One family, one grid, no mixing weights.
The five discipline glyphs (shoe, heart, dumbbell, horn, book) have fixed identity and appear at consistent weight in the band header, the discipline buttons and the gain bubbles.

**D-112. Primary buttons carry a hard-edged diagonal sheen.**
A single crisp boundary at roughly 116 degrees from the upper-left, 30% white, covering the upper-left band only. The gradient stop must be a split at one percentage (`34% 34.5%`), not a feathered range. A vertical light-to-dark gradient is explicitly **not** compliant: that is the generic CSS button, and the point of the rule is the moulded-plastic read. Fixed at 30% white in both themes.

**D-113. Stat band headers use the six per-stat tints, and nothing else may.**
The tints in `DESIGN.md` §3.6 are near-invisible individually (1.04-1.14 contrast against the panel) and only read as a group. They are wayfinding, not identity: the label and glyph remain primary, and a tint must never be the only thing distinguishing two columns (D-12).
These tints are the **only** place a per-stat colour may appear. Saturated identity hues remain banned by D-114.

**D-114. No saturated per-stat hue.**
Every saturated hue in the system is already semantic (§3.3). A stat may carry a tint (D-113) but not a colour. This rule survived the tint change deliberately: the collision that killed the earlier draft, Guts sharing `#800014` with the failure-risk colour, is exactly what this prevents.

**D-115. Run cards carry a micro-grade strip.**
Five 18px grade chips in the fixed order Speed, Stamina, Power, Guts, Wit; no values, no labels, order never varies; a 4px dot marks a stat past 1000. The strip is `aria-hidden` and duplicated as visually-hidden text, because a scannable pattern that only works by sight fails P5. Values remain on the card; the strip is a shortcut, never a replacement. No sparkline and no radar chart on a run card (D-94).

**D-116. Grade badges stay subordinate to the value.**
22px in the band, 18px in the micro strip. A grade badge that optically matches the numeral beside it has broken P3.

**D-117. The guided flow has no numbered step sidebar.**
No vertical 1-2-3-4 rail with a highlighted current step. That shape is a SaaS setup wizard and it is the fastest way for the flow to stop reading as an in-game event. One `Step 1 of 3` line with three dots at the foot of the stack. The client's own event screen presents a question and its options, and its only progress cue is the turn counter.

#### Audit of these five rules against `antislop-ui`

Checked on 2026-09-27, because a rule written to defeat one slop pattern can install another.

| Rule | Verdict | Note |
|---|---|---|
| D-110 rounded font | **Pass** | `antislop-ui` does not ban Inter or Roboto on principle; it bans "the font that shows up because it was the default". This rule bans them *with a measured brand reason*, which is what the skill asks for. Rejecting Varela Round and Quicksand on weight coverage is an evidence call, not a taste call. |
| D-111 chunky icons | **Pass** | Directly answers the *Lucide Icons* tell. One internal tension found and fixed: §6.0 of DESIGN.md bans generic sparkle glyphs while §7 permitted a sparkle mark. Resolved by narrowing the sparkle to `is_unique` alone, where it denotes rather than decorates. |
| D-112 hard sheen | **Pass** | A specific material with a measured referent, not a default. The risk is the reverse one: a soft feathered gradient masquerading as compliance, which G-24 checks by inspecting the gradient stops. |
| D-113 per-stat tints | **Pass, with the gate carrying the weight** | This is the rule most likely to slide into *Too Many Colors* and *Excessive Accent Color*. It survives only because the tints are sub-perceptual as colour (1.04-1.14 against the panel) and because D-114 still bans saturated stat hues. Without G-25 sampling the range, someone would "improve" these into a rainbow within a week. The gate is the rule. |
| D-115 micro-grade strip | **Pass** | Derived from stored values, so it is not the *Stat Cards With Invented Numbers* tell. The `aria-hidden` plus visually-hidden text requirement is what keeps it out of *colour-only* territory. |
| D-117 no step sidebar | **Strong pass** | The numbered 1-2-3 rail is the *How It Works Always 3 Steps* tell in app clothing. Removing it is the correct instinct, not a simplification preference. |

One residual risk worth naming rather than legislating away: `antislop-ui` also warns against the *Sterile Default*, over-filtering into a flat white nothing with no identity. This system is dense with identity devices (brown ink, lattice, ribbons, arrow-caps, sheen, faceted field, six tints). The failure mode from here is excess, not sterility, which is why §7's two-motif budget and §7.1's colour caps outlive this round of additions.

---

### 10e. Event resolution and scenario flows

**D-130. Four flows, one panel.** F1 standard turn, F2 character event, F3 support card event, F4 scenario event. F2 and F3 share the DESIGN.md §8.6 event panel and differ only in the tag ribbon text and the outcome list. A fifth random-world flow is not modelled, because no in-run dataset supports it: `UMAMUSUME_REFERENCE.md` §1.4.4 carries three chains and §3.4 is live-ops content.

**D-131. The event panel is an overlay, not a modal.** The training scene, the turn chip and the stat band stay visible behind it. A full-page scrim that blanks the run destroys the orientation the client deliberately preserves.

**D-132. Every choice previews its full outcome before commitment, and committing is a separate action.** Applies to all four flows without exception. D-51 extends to events.

**D-133. Delta colour is fixed by the client, not by web convention.** Gains orange, losses blue. Green means action and red means risk. A green up-arrow in an event preview is a review failure.

**D-134. A stepped flow is allowed only for F4, and only when the event genuinely has multiple parts.** D-117 still bans a step rail on a standard turn. A scenario sheet keeps the tag ribbon, the banner choices and the scene behind it. It gains no progress rail and no Back / Next footer.

**D-135. Scenario sheets are data-driven.** No game mechanic is hardcoded into a scenario UI. Where the brief and the cited source disagree about a mechanic, the sheet renders what the record supplies and the disagreement is raised rather than silently resolved by a wireframe. Live instance: summer camp, `UMAMUSUME_REFERENCE.md` §1.1.2 versus the brief.

**D-136. Never hardcode a run length.** No source in this repo records a total turn count and the brief's "~50 rounds" is unverified. The turn chip shows a turn number and a remaining-turn count, as the client does (`DESIGN.md` §6.6 reads both off frame `194819`, plus a second chip for the phase countdown), and derives nothing from an assumed total.

**The remaining count is entered, not computed — this is the arithmetic the earlier wording hid.** "Turns left" looks like `total − current`, and with no recorded total that subtraction has no operand. So the value is a Trainer-entered reading of what the client printed for that turn, stored per turn like any other observed figure, on the D-270 pattern: the tool records what the client showed and never derives it. Two labels must stay distinct in UI and in schema, because they are different claims — remaining turns *in the current period*, which the client's calendar structure implies, and remaining turns *in the career*, which nothing in this repo records. A single field named `turns_left` without a period would silently assert the second one. G-31 stays as the check; the superseded `screen-a-dashboard.html` hardcoded `3` and is the reason it is a gate rather than a preference.

**D-137. `TurnEvent` needs a PRD citation and Architect sign-off before any migration.** `AGENTS.md` makes an uncited table a no-merge. Its `deltas` column stores observed outcomes only; `turn_entries` remains the source of truth for absolute values.

**D-138. Event copy is unverified until sourced.** Event titles, choice labels and mood words appearing in a mockup are sample content and may not become UI strings without a citation (D-20).

---

### 10f. Race calendar and fan gating

**D-150. The race calendar is the client's component, not a new one.** 24 half-month cells under a Junior / Classic / Senior tab bar, as measured in `Screenshot 2026-07-17 230755.png`. Seven cell states are defined in DESIGN.md §6.20 and a cell must always be in exactly one of them.

**D-151. A fan lock shows the number.** `fans_needed` appears on the cell, not just a padlock. Grades 300, 400 and 700 all bottom out at 350 fans, so a lock alone cannot identify which race is blocked or by how much (`UMAMUSUME_REFERENCE.md` §1.2.6).

**D-152. The maiden gate is a distinct state from a fan lock.** The client requires a Debut or Maiden win before standard races are open, which is a conditional lock unrelated to fan count. Rendering it with the fan treatment sends the Trainer to check the wrong number.

**D-153. Tier labels are per-race claims, never inferred from a grade code.** Only grade 100 = G1 and grade 400 = OP were client-confirmed when this was written; codes 200, 300 and 700 were `❌ UNVERIFIED`, so a UI mapping them was asserting a fact the repo did not have. That ban on inference stands.

**Dated per-row exceptions, measured 2026-09-29 (R72, nullified the same day by R75; `race-tier-labels-2026-09-29.json`).** The rule was tested against two publishers read in a rendering browser, joined on the race's own name rather than its numeric code: G1 agrees on **34/34**, G2 on **42/42**, G3 on **76/76** rows of the URA schedule, so those tiers are now per-race sourced and the seeder carries no code-to-label constant at all. **Open and Pre-OP are not settled by this.** Game8's table is graded-only (161 rows: G1 43, G2 42, G3 76), so its silence on Open and Pre-OP is silence, not agreement; and uma.guide alone labels Open as `"OP/L (Open/Listed)"`, a label that conflates two classes. Three Open rows are pinned per row by their own client name carrying 「オープン」, and those three keep the label. The other 115 code-400 rows and all 26 Pre-OP rows are **null**.

**R75 is the owner's ruling on the remaining 115, and it reverses what Slice 14 did with them.** Slice 14 kept the label as a *disclosed generalisation* from the three (`scope: code-level-client-naming-pin`, `per_row_sourced: false`) on the grounds that nulling it dropped a tier the tool had shown correctly since Slice 11. The owner ruled the other way: a tier the export's numeric grade implies but no source states for that race is not seeded, and it is not kept on a disclosure flag either — **a null tier is the honest state**, so the flag is gone from the extraction rather than set to `false`. Seeded distribution is G1 34, G2 42, G3 76, OP 3, null 141; where the tier is null the seeder writes `slot_label` = `Race`, which is what a row with no per-race label has.

**Publisher dates are asymmetric and stay that way in the record:** Game8's page is dated 2026-09-09, which clears the 2026-07-01 bar; uma.guide publishes no date anywhere — no `Last-Modified`, no time element, no generated-at field in the dataset chunk — so only its fetch date is knowable.

**D-154. Race selection is F5 and reuses the event panel.** It appears only on a turn where the calendar holds an entry. Skip is a first-class option with its own banner, never the absence of a click.

**D-155. No race outcome prediction, ranking, or win estimate.** `PRD.md` §6.11 is unmodified on this point. ADR-0001 lifted the non-goal for Energy guidance only and explicitly left race outcomes out of scope. Eligibility arithmetic is permitted; placing projection is not.

**D-156. Fan count is persistent in the run header**, using the client's own sentence form, target plus shortfall (`194819`). No progress bar: the calendar has several stacked thresholds and one bar implies one ceiling.

**D-157. Unverified race claims are attributed, not asserted.** "Up to 2 or 3 races per turn" and the Senior-year Arima Kinen requirement have no source in this repo. If they appear in a design artifact they are labelled as owner-supplied, per D-20.

**D-158. Race and fan data need a PRD requirement before any migration.** `turn_entries` has no `fans` column and there is no `race_goals` or calendar table. `PRD.md` US-10 is P2 and deferred. Building this UI in Phase 1 means promoting US-10 to P1 and writing an FR for it, which is an owner and Architect decision, not an implementation detail. See ADR-0001.

---

### 10g. Scenario awareness

**D-160. The active scenario is a header element, never a subtitle.** Four scenarios are live on Global and they cap differently. A run view that does not name its scenario at header weight reads as a single-scenario tracker.

**D-161. Stat bars scale to that scenario's per-stat base cap, never to a global constant.** A Unity Cup Wit bar and a Ura Finale Wit bar showing the same value must be visibly different lengths. This is the cheapest honest signal of scenario awareness.

**D-162. A cap is displayed as a stack, not a number.** A run's true ceiling is scenario base plus breakthrough at +16 per ★3 inherited factor plus per-stat support-card 限界値アップ effects (`UMAMUSUME_REFERENCE.md` §1.3.4), applied at three separate moments. Where a component is not tracked in the schema, the disclosure line says so. An unqualified "Cap: 1800" is a false claim of completeness.

**D-163. Single-source cap data carries its confidence.** Grand Masters and L'Arc caps are recorded in §1.3.4 as "single-source, not corroborated". They may appear only with that stated. The four primary scenario cap sets come from sources flagged `STALE` and need re-verification before implementation.

**D-164. Scenario must become resolvable before any of this is built.** `training_runs.scenario` is a nullable free-text string. Free text cannot select a cap set. Either an enum or a `scenarios` reference table is required, and cap data needs provenance rows like any other engine-owned fact. See `docs/adr/0002`.

**D-165. No scenario-specific chrome may be invented from assumption.** The entire screenshot corpus is one scenario, Unity Cup. There is no visual evidence for the UI of the other three, nor for differing facility layouts. Scenario-specific interface treatment is either captured from the client or designed from scenario data, and a mockup must label which.

---

### 10h. Persistent resources and the conditional race step

Owner decisions of 2026-09-27, recorded in `docs/adr/0003`. These are requirements, not options.

**D-170. Turn, scenario, Energy and Fan count are persistent in every run view.** None of the four scrolls away. A Trainer mid-turn needs all four to decide the next action, and the schema now stores Energy and Fans as end-of-turn totals so the header shows a real value rather than a derived sum (`ADR-0003`).

**D-171. Energy is prominent at the point of commitment.** In the guided flow the gauge and the caution state sit adjacent to the Confirm control, not only in the page header. A gauge 800px from the button does not answer "can I afford this" at the moment it is asked.

**D-172. Energy and Fans store absolute end-of-turn values, not deltas.** This matches the existing convention for the five stats, which is why an injury is already representable as 302 then 294. A delta series cannot answer a start-of-turn value without a summation that every prior row must survive intact. Deltas stay a presentation-layer subtraction.

**D-173. The three race lock states are visually distinct, and the maiden lock breaks the padlock pattern.** Mandatory Goal races read heavier, never dimmer. Fan locks show the numeric `fans_needed`. The maiden gate uses a dashed outline and its own sentence, because its remedy is an event rather than a quantity. Sharing one treatment between the two locks is a review failure.

**D-174. Locked races stay visible and named.** A fan-locked race renders disabled with its number, never hidden. A race the Trainer did not know existed cannot be planned for.

**D-175. The race selection step is conditional on the calendar holding an entry for that slot.** On turns with no race the flow goes straight to training or rest. A race step on an empty slot is noise that trains the Trainer to ignore the step when it matters.

**D-176. `Skipped` is a stored status, not an absent row.** `race_entries.status` carries `NotOffered`, `Skipped`, `Entered`, `Completed`. A nullable-absent row cannot distinguish "not offered" from "declined", and that distinction is exactly what the calendar renders.

**D-177. Screen D is a generic multi-step scenario event.** No scenario mechanic is hardcoded into the UI. The Summer Camp "choose 2 disciplines" instruction was withdrawn by the owner on 2026-09-27; `UMAMUSUME_REFERENCE.md` §1.1.2 stands.

**D-178. US-10 is P1.** Race calendar and fan gating are Phase 1 scope. Any artifact that treats them as deferred is out of date.

---

### 10j. Corrections taken from the screenshot corpus

Recorded because each of these was visible in the client and my earlier specs got it wrong anyway. A rule that names its own predecessor is easier not to repeat.

**D-185. Grade badges are tinted per grade.** A orange, B pink, C green, D blue, E purple, F and G grey. An earlier revision shipped one flat indigo badge on the argument that the hues were semantically spoken for; that overrode a direct observation recorded in `RAW-FINDINGS.md` §8. The letter always accompanies the colour, so nothing depends on hue discrimination (P5, D-12).

**D-186. The Energy fill sweeps the full gauge spectrum.** Cyan through green and lime into yellow, amber and red toward the right end. Colour encodes **position on the track**, not the current level. An earlier spec said "cyan to lime", which was a measurement artefact: the probe only covered the filled portion of a partly-drained bar, so the amber and red tail sat behind the dark unfilled track and went unmeasured.

**D-187. The discipline banner carries the facility activity name.** Two stacked ribbons: the pale one names the discipline and level, the saturated one names the activity the facility offers (`Breaststroke`, `Freestyle`, `Incline`, `Running`, `Long-Distance Swimming`, `Dirt`). A mockup showing only `Stamina Lvl 1` is incomplete.

**The closing clause of this rule was an over-reading and is withdrawn.** It previously said the activity set is "the best evidence in the corpus that scenarios differ in facility layout". Every frame carrying these six names is **Unity Cup**; the corpus has no Trackblazer or Our Grand Concert training HUD (`SCREENSHOT-MANIFEST.md` coverage table), so within-corpus evidence for the list is variation *per facility within one scenario*, not variation *between scenarios*. What survives: the activity line is required content on the banner, and a future cross-scenario claim needs frames or scenario data behind it, not this list.

**D-188. The support card rail belongs on the dashboard.** Circular avatar, discipline type badge, segmented bond gauge, orange double chevron when friendship training is available, flame mark when the card sits on its own tile. Documented in `RAW-FINDINGS.md` §3.6 and then omitted from every mockup in this package.

**D-189. The failure indicator is a blue pill beneath the stat band.** The client shows a computed percentage because it holds live state. This tool renders the §6.15 band word and **never** a number, because no sourced curve exists (D-155, `ADR-0001` §3).

**D-190. Advisories are NPC speech bubbles, not banners.** A circular staff avatar beside a rounded white bubble with a small green `HINT` badge, which is how the client frames every piece of coaching it gives. This is the single largest immersion gain available and costs nothing, because the alternative is a validation banner that reads as software complaining.

**D-191. A cap increase is never phrased as a stat increase.** The client separates the two: `Max Energy +4` against `Guts +10` in choice previews, and the effect class 「限界値アップ」 rendered Global as `Max Speed`, `Max Stamina`. So a log line reads `Speed cap went up by 4`, never `Speed went up by 4`. Conflating them corrupts run history. Exact client log phrasing is unobserved, so the wording is ours and the distinction is not negotiable.

**D-192. Unique skill chips use a pink-to-blue gradient fill.** An earlier revision flattened this to `indigo-50` on the theory that pink and blue were reserved; those hues are semantic only when used as *meaning*, and a chip background carries no meaning beyond identity. The gradient is the most legible affordance on the skill panel.

**D-193. Scenario claims must name their evidence.** The corpus is predominantly Unity Cup with confirmed Ura Finale material and **no Trackblazer or Our Grand Concert frames**. A `Result Pts` counter and a `TEAM RANK` badge are observed Unity Cup chrome. Any design asserting a third scenario's UI is describing something this repository has not seen.

---

### 10k. Strategy-driven UI rules

Extracted 2026-09-27 by reading the strategy wikis in a real browser. Note for the record: the assumed blocking was not real. GameWith and Game8 both served these pages to a plain Playwright navigation with no user-agent substitution, no injected delays and no scroll tricks. The earlier 403/503 reports came from simple HTTP fetchers failing to execute JavaScript, which is a rendering limitation, not access control. Nothing was bypassed.

Sources read: `gamewith.jp/uma-musume/article/show/257614` (training guide) and `game8.co/.../archives/536322` (Global Special Week build guide).

#### Fail probability on event choices

**D-200. A choice preview must show both the success and the failure branch, or say that the choice cannot fail.**
Game8 documents event outcomes as split branches: `Choice 2 (Fail)` yields `-3 Mood · 3 Random stat -10 · Practice Poor`. GameWith documents the same shape. A preview showing only the success numbers is not a preview, it is a best case presented as an expectation, and it is the one UI lie this tool must not tell, because the whole point of preview-before-commit is informed consent.

Required form: a success block, a failure block, and a probability label where one is known. Where the client exposes no probability for that specific choice, render both branches with no percentage and state that the odds are not shown, rather than inventing a number (D-155).

**D-201. Failure outcomes are first-class log entries.** `Practice Poor`, a mood drop, and a stat hit on the last-trained stat plus two random others are recorded facts about the run, not error states. They render in the timeline with the same treatment as a success, using blue for the decreases.

#### Mood

**D-202. Mood is both a tier and a number.** Wikis record mood changes as integer deltas, `-2 Mood`, `-3 Mood`, and `+ 20%` style training modifiers. The mood pill shows the tier word; a mood change in a preview or log shows the delta. Both, never one alone. The pill's directional arrow is the third required part of the tier readout (D-259).

**D-203. Withdrawn 2026-09-27, superseded by D-259.** This rule blocked the five tier labels as unverified Global client strings. An owner-supplied capture of the client's Mood Effect panel resolves it: the tiers are `GREAT`, `GOOD`, `NORMAL`, `BAD`, `AWFUL`, with training effects +20 / +10 / 0 / −10 / −20 percent and pre-race attribute effects +4 / +2 / 0 / −2 / −4 percent, and the active tier marked on its own row. `DESIGN.md` §6.17 carries the table and D-20 now records the resolution rather than the block.
The rule's speculation is withdrawn with it: `Practice Poor` is **not** a candidate for the low mood tier. It is a failure condition from an event outcome (D-201, `DESIGN.md` §6.16b), and the client's low tiers are `BAD` and `AWFUL`. A mood widget must never offer a `Practice Poor` state, and the two vocabularies must not be merged.

#### Energy

**D-204. 50 is the only sourced Energy threshold. There is no 30.**
A full sweep of the GameWith training guide returns `体力50以上をキープしたい` and `練習の成功率は体力50以上とそれ以下で大きく変わってくる`, and no 30-Energy rule anywhere. The Caution band at 50 is sourced. A Danger band below 30 is an **owner preference** and must be attributed as such wherever it renders, never presented as game mechanics.

**D-205. Rest is not a safe action.** `お休み` returns +30 Energy but can produce 夜更かし気味, a stayed-up-late penalty. A UI that presents Rest as pure recovery is wrong. The Rest option carries the same success/failure branch treatment as any other choice (D-200).

**D-206. Energy is a running total shown before commitment.** Per D-170 to D-172. The client's own guidance is forward-looking, keep 50 or more so a strong friendship session is not wasted, which is exactly the decision the guided flow makes at the moment of commitment.

#### Scenario and camp

**D-207. Camp is two things and it is automatic.** `夏合宿` (Summer Camp) and `海外遠征` (Overseas Expedition), four turns, **every training level maxed at once**. There is no discipline selection. GameWith's strategic advice is to use camp to cover the disciplines you cannot normally train, which is a planning prompt the UI can support, not a mode the UI should encode.

**D-208. Goal races carry recommended stat targets.** Game8 gives per-race figures: `at least 500 stamina before the Kikuka Sho on Classic Year Late October`, `at least 600 before the Tenno Sho (Spring) on Senior Year Late April`. Where a goal race is known, the calendar cell and the race selection banner should surface the recommended value alongside the hard `fans_needed`, since one is an obligation and the other is advice, and they must not look identical.

**D-209. Arima Kinen is a real Senior Year Late December goal race.** An earlier note in this package marked the owner's claim unverified. Corrected: Game8 lists `Arima Kinen (Long - 2500m) Senior Year Late Dec` as a career goal. It is character-specific rather than a universal scenario-clear requirement, which is a distinction the UI should preserve.

#### Sample data

**D-210. Use real Global strings in prototypes and mockups.** Now evidenced from Game8 and usable under D-76: skills `Gourmand`, `Unstoppable`, `Up-Tempo`, `Come What May, See Ya Later!`, `In Body and Mind`, `Homestretch Haste`, `Professor of Curvature`, `Traightaways`, `Playtime's Over`, `564 Escapades`; cards `Oguri Cap (Ashen Miracle)`, `Oguri Cap (Starlight Beat)`; statuses `Practice Poor`. Invented names remain banned.
⚠️ **`Light Hello` removed from this list.** It was recorded here as a skill name. It is not one: `UMAMUSUME_REFERENCE.md` §3 lists **Light Hello [From the Ground Up]** as a **Pal support card** (live on Global 2026-07-22), and `scenarios.json` carries Light Hello as a **character** (`char_id 9008`) linked to two later scenarios. Both readings rule out "skill", so this was a misclassification on our side, not a wiki trap — do not use it as a skill string until re-evidenced from a skill list. `Homestretch Haste` stays: `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` section "Trackblazer (uma.guide)" corroborates it independently as the Legendary epithet reward.
⚠️ **Three of the ten skill strings above are not client strings — corrected 2026-09-29 against the data export (`ADR-0011`).** D-210 sourced these ten from a publisher's skill list, and the list's renderings differ from what the client prints: the export's `name_en` is the client string while `enname` is a literal translation of the Japanese, and the two diverge on 535 of the 623 `[Global]` rows. So `Come What May, See Ya Later!` is **`Come What May`** (id 201701; `Prepared to Die` is the translation), `Playtime's Over` is **`Playtime's Over!`** (id 201661), and **`Traightaways` appears under neither field at all** — the nearest real families are `Straightaway Adept` (200362), `Straightaway Acceleration` (200372) and the `Sprint/Mile/Medium/Long Straightaways ◎/○` distance skills, and picking among them would be guessing at a provenance, so it is withdrawn rather than replaced. The other seven check out exactly as recorded (`Gourmand` 201351, `Unstoppable` 202371, `Up-Tempo` 200722, `In Body and Mind` 200511, `Homestretch Haste` 200512, `Professor of Curvature` 200331, `564 Escapades` 110071), which is the useful part: the list was right about seven and wrong about three, and the gate read all ten as real. **Standing consequence for G-16:** a name evidenced from a publisher's *list* is not the same as a name evidenced from the client — G-16 accepts a string against this rule only when it is the export's `name_en` of a row available on `[Global]`, and `SkillSeeder` now holds exactly those.

---

### 10l. Scenario mechanics extracted from the Game8 scenario guides

Read 2026-09-27 in a real browser. **Coverage is uneven and that is recorded rather than smoothed over:** the URA Finale guide (`archives/536520`) was retrieved in full. The Unity Cup guide (`archives/545572`) and the Trackblazer guide (`archives/580723`) both redirected away from their scenario pages during the session, so **neither scenario's own guide was actually read.** Anything below about Unity Cup or Trackblazer comes from the screenshot corpus or `UMAMUSUME_REFERENCE.md`, not from these two URLs. Do not cite 10l as evidence for them.

#### The 1200 line is a real soft cap, not an app artefact

**D-211. Gains past 1200 are halved, and the UI must say so.**
*"Training gains for stats beyond 1200 are always halved."* 1200 is therefore a meaningful in-game inflection point, not just a validation constant this app happens to use.

**Attribution corrected 2026-09-27; this rule previously cited its own source wrongly.** The text here used to read "The URA guide states plainly", and `docs/scenarios/01-ura-finale.md` contains neither the word *halved* nor the figure *1200* anywhere. The sentence is from **Game8's `[Global]` English scenario pages**, read in a browser — `archives/536520` per `docs/adr/0002` amendment 1, and the same phrasing recorded in `UMAMUSUME_REFERENCE.md` under "The July 1, 2026 Global rework" against Unity Cup `545572` (2026-07-07) and Trackblazer `580723` (2026-08-25). The rule stands on a post-rework `[Global]` source; what was wrong was the pointer to it.

The second clause was also misattributed: `Fully Charged` is not in the URA guide either. It is a **Grand Live / Our Grand Concert** mechanic that pays off at Power 1200 or more (`UMAMUSUME_REFERENCE.md` §1.6 conflict 4, still OPEN on its mechanics). Same threshold, different scenario, and the tool must not present a Grand Concert gate as URA documentation.

**Two axes, one threshold — do not merge them.** This rule is about *training gains* being halved past 1200. `UMAMUSUME_REFERENCE.md` §1.3.4 separately records that points past 1200 "have a **reduced effect on the Umamusume during the race**", sourced from Game8 2025-11-21 and Kamigame 2024, both ⚠️ STALE. Those are different claims about different phases of a run, and §1.3.4's baseline row currently states only the race-side one. A UI sentence like "gains halved past 1200" asserts the training axis; it must not be quoted as though it were the race-effect figure.

**Verification status, stated so it is not read as equal to D-212.** The additive cap model in D-212 is confirmed at the data level by `scenarios.json`. This rule's *threshold* is corroborated by the same export (every scenario's cap reproduces as `1200 + stats[i]`), but the **halving itself is prose-only** — no dataset field carries a 50% factor, and `archives/536520` has no recorded date, which D-228 would ordinarily require. So: render the 1200 marker as sourced, attribute the halving to a `[Global]` guide rather than to game data, and treat the exact reduction as unconfirmed pending re-verification.

This reframes `ADR-0002`. The defect there is real, the app rejects values a real run reaches, but the fix is not "raise the number to an arbitrary 2000". The bar should show **1200 as the soft-cap line where returns halve**, which is a fact about the game, and separately show the scenario ceiling, which is a different fact. Two markers, two meanings.

**D-212. Caps are expressed as base plus bonus, and the client renders the bonus in gold.**
*"The URA Finale Scenario now has +200 stat caps for Speed, Stamina, Power, Guts, and Wit."* So URA's 1400 is `1200 base + 200 scenario`, not a flat 1400. The cap stack in `DESIGN.md` §6.22 is confirmed as the right model, and its rows should read as additions, not totals.
**Confirmed at the data level.** `scenarios.json` stores a five-element `stats` array per scenario that is exactly this bonus, and JP scenario id=8 carries a **negative** Stamina bonus (`-200` → a cap of 1000, below the base). A field that can sit under the base is a delta, which settles the model rather than merely corroborating it. Two consequences: a cap row must be able to render a ceiling **below 1200** without breaking, and the additive display is the honest one.

**D-213. Gold text is the client's own signal for a cap increase. Adopt it.**
*"When a stat's cap is about to be increased, the values that will be added will always be indicated in gold text."* This resolves the open wording question in D-191, which had said the distinction was real but the client's phrasing was unobserved. It is now observed: **cap increases render in gold.** So a `+4` in `gold` means the ceiling moved and the stat did not, and a `+4` in `orange` means the stat moved. The colour carries the distinction the prose was carrying, which is stronger than a wording rule. Keep the word `cap` in the text as well, so the distinction survives for anyone who cannot separate gold from orange (P5, D-12).

#### Scenario structure

**D-214. Summer camp runs in both Classic and Senior years, four turns each.**
*"There are 4 turns of each Summer training and they occur during Early July until Late August of both Classic and Senior Years."* That is eight camp turns across a run, not one window. `UMAMUSUME_REFERENCE.md` §1.1.2 describes the window without saying it recurs per year, so this is a genuine refinement. The calendar must mark both windows.

**D-215. Scenario NPCs appear in training at calendar milestones, and they are the speech-bubble speakers.**
The URA guide names Director Akikawa, who appears in training after the debut and gives +30 Energy on the third camp turn, and Reporter Etsuko Otonashi, who appears from Early July. These are the characters behind the `HINT` bubble pattern in D-190, and **which NPC appears is scenario-specific**, so the advisory speaker is scenario chrome, not a fixed avatar. Note the rights position: the client's NPC artwork is not ours to ship, so the bubble carries a name and a monogram, not an illustration.

**D-216. A Scenario Link is a per-scenario property that some scenarios do not have.**
~~Each scenario has a Scenario Link Character.~~ **Withdrawn.** URA Finale has Aoi Kiryuin and Unity Cup has five linked characters selected through Team Name, but **Trackblazer has none**, and `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` states it directly: *"no new original story characters and no Scenario Link mechanic."* The game data agrees without interpretation — Trackblazer's `scenario_linked_characters` is an empty array where URA's has one entry and Unity Cup's has five. So scenario link is an **optional, variable-cardinality** field: the run header must render zero, one, or many, and any layout that reserves a slot for exactly one link character is wrong on the third scenario. This is the same class of defect as D-220's fixed resource strip, found a rule earlier.

**D-217. Event choices are documented positionally as Top, Mid, Bottom.**
The guide writes outcomes as `Choices: Top: Stat +10 / Mid: Energy +20 / Bottom: Skill Points +20`. So the client presents a vertical stack of up to three, and `choice_index` in `ADR-0003` maps cleanly onto that. Design the choice list for one to three options, not a scrolling list of many.

#### Staleness warning

**D-218. URA Finale was reworked on 2026-07-01 on Global, ahead of its planned release.**
*"Updates to the URA Finale scenario were made on July 1, 2026 in the Global version."* The cap table in `UMAMUSUME_REFERENCE.md` §1.3.4 traces to Game8 2025-11-21 and Kamigame 2024-02-19, both already flagged `⚠️ STALE`, and the URA row is demonstrably from before a rework that changed caps.
**Re-verification is now done, and it moved off prose onto data.** The live source is GameTora's `scenarios` dataset (manifest key `scenarios`, hash `61b7c51c` as of 2026-09-27), which carries `stats` as a per-stat bonus over a 1200 base and `hard_caps` as a separate 2000 ceiling. Its four Global rows reproduce the published post-rework caps exactly. **Seeding must trace to that dataset, not to §1.3.4**, which is stale in two ways: it predates the rework and its Climax row is transposed. See D-227 for the durable provenance rule and D-228 for the dating rule.

---

### 10m. Scenario divergence rules

Derived from `docs/scenarios/01-ura-finale.md`, `02-unity-cup.md`, `03-trackblazer.md`, then corrected against the three publisher sections of `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` and the live `scenarios.json` dataset. Full analysis: `docs/design-research/SCENARIO-DIFFERENCES.md`.

**D-220. The persistent resource strip is scenario-composed, never fixed.** Ura Finale has no team system, no shop and no mid-scenario currency, so its dashboard is the minimal layout: turn, trainee, scenario, Energy, Fans. Unity Cup adds Team Rank, Spirit Burst count and Result Pts. Trackblazer adds Grade Points against deadlines and a Shop Coin balance. A URA mockup showing a team panel is a factual error, not a stylistic choice. The strip must stay composable with a **fourth** scenario in place — Our Grand Concert is already live on Global and is not represented here at all.

**D-221. The Race Calendar is conditional on the scenario having race goals.** Trackblazer has none; it substitutes Grade Point deadlines, a shop and Rival races. `scenario_races` and `race_entries` from `ADR-0003` are URA-shaped and must be generalised to a `scenario_slots` table with a `kind` discriminator (`GoalRace`, `TeamRace`, `GradeDeadline`, `ScriptedEvent`) before any scenario other than URA or Unity Cup can render a timeline.

**D-222. A facility level chip must say what drove it, and there are now three possible causes.** URA derives level from repeating one stat four times. Unity Cup derives it from the team's aggregate rank in that stat (`G/F=1, D/E=2, B/C=3, A=4, S=5`) — the identical label `Lvl 5` therefore means something opposite to the URA case. Trackblazer uses the URA rule **and additionally allows permanent purchased levels** (Training Application items, 150 coins each), so a Trackblazer Level 5 can be partly bought rather than earned. A level without its cause is misleading in at least one of the three, and the purchased path is the one no other scenario has.

**D-223. Spirit Burst state is a six-state machine and "spent" is not terminal.** Chargeable (white flame) → charged → held (charged, deliberately untriggered) → normal burst spent → **Extreme chargeable** → Extreme spent. The 2026-07-01 update made an **Extreme Spirit Burst available on a teammate's next Unity Training after their normal burst**, which invalidates the pre-patch rule *"each character only gets one Spirit Burst per career"*. Rendering a spent teammate as a dead end is now factually wrong.
Two further pre-patch carry-overs corrected here: **the additional energy cost on Special Training was removed**, so the preview must not apply an energy penalty (the Wit case survives only as a burst bonus of **+5 extra energy recovery**), and burst skill hints are **no longer random** but draw from that support card's own hint pool, falling back to A-rank aptitude when exhausted — the tool may name the pool but must never predict the draw (Rule 1).

**D-224. Fan thresholds come in two unrelated kinds and must not share a widget.** Race *entry* gates run 350 to 25,000 (`UMAMUSUME_REFERENCE.md` §1.2.6). URA *event* gates for Unique Skill upgrades run 60,000 / 70,000 / 120,000 Turf and 40,000 / 60,000 / 80,000 Dirt. The next threshold the Trainer cares about depends on which kind is imminent; a single `Fans: 1,943` readout serves neither.

**D-225. Race advice is scenario-gated or must be withheld.** In Trackblazer, racing often is the strategy and fans are the payoff. In Unity Cup, *"do not try to maximize regular races"* because every racing turn is a turn not spent on team rank. The same suggestion is correct in one scenario and harmful in the other.

**D-226. NPC presence is itself scenario-specific, and friendship plus burst state are logged as typed `turn_events` payloads, not `turn_entries` columns.**
~~NPC friendship is a universal run resource.~~ Corrected: **Chairman Akikawa is absent from Unity Cup** — Riko Kashimoto stands in as acting chairman, which is why Unity Cup's April Unique Skill level-up carries **no bond condition** while URA's does. So a gauge for an NPC who is not in the scenario is worse than no gauge, and the set of tracked NPCs is a per-scenario list, not a constant. The storage rule is unchanged: both friendship bars and burst state are scenario-specific per-turn facts, so columns like `akikawa_bars` or `bursts_triggered` on a shared table would push Unity Cup and URA chrome into every run's core record.

**D-227. The cap conflict is closed, and the fix is a provenance rule rather than a number.**
Resolved: Global Unity Cup caps **are** `1300/1300/1300/1300/1800` and the 1,800 Wit denominator in `DESIGN.md` §6.22 and the v7/v8 mockups stands, confirmed by `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` section "Unity Cup (GameTora)" (dated after the patch) and by `scenarios.json`. The durable rule is the one that would have caught it sooner: **every stored game value needs a server qualifier and a date.** `UMAMUSUME_REFERENCE.md` §1.3.4's Climax row had neither, and it is both stale and transposed — it reads `…/1500/1200` for Guts/Wit where the game data is `…/1200/1500`. A value that cannot state which server it describes and when it was true is not seedable.

**D-228. A source's authority is its date, not its publisher, and a correct-as-written prediction still expires.** `03-trackblazer.md` described an unreleased scenario six months after it shipped. the Trackblazer GameTora section of `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` is dated the launch day itself and predicts Global will keep the flat 1200 caps — a reasoned prediction that the 2026-07-01 rework then overturned. Both were doing their job honestly; both are now wrong on numbers. Any rule, mockup value, or seed trace that depends on a dated claim must re-check on a source dated **after** the most recent patch it knows about, and hedged language ("expected", "may be adjusted", "check current patch notes") is the signal to re-check first.

**D-229. Surface hidden information the client obscures.** The Unity Cup guide records that the Unity Training icon overlay hides skill hint icons on facilities, forcing manual inspection. Where the tool can show a fact the client makes hard to see, that is the value being designed for, and it outranks visual fidelity to the client's layout.

**D-230. A probability may print as a number only when it is sourced from client data or from two independent sources — and then derive it from entered turns.** `ADR-0001` concluded training failure rates could not be sourced and had to ship as bands. Trackblazer's **Race Fatigue** table is a different case and is fully quantified: it keys on the count of **consecutive races** (1 race: 0-15% mood down; 2: 0-33%; 3: 60-90%+; **4+: 100%**, plus 33% Skin Outbreak and 40% for 3 random stats −10). Because consecutive-race count is already recoverable from `turn_entries`, this satisfies Planner Rule 4 — it is a pure function of logged turns, not a simulation.

**Amended 2026-09-28 (R48), and the amendment narrows what this rule authorises.** The clause
above is false as the schema stands: `race_entries` links to a `scenario_slots` row (month, half,
tier) and never to a turn, and the guided flow offers no race choice, so no logged turn can be
identified as a race turn. The count is therefore **entered**, as a `RaceFatiguePayload`
(`{consecutive_races}`) on the turn the Trainer says it applies to, and KI-17 carries the gap so a
later slice can close it with a real link rather than a guess. Two things do not change: the number
is still a pure function of what the Trainer logged, so it is not a simulation, and the surface
still prints one qualitative word for the band rather than the published percentages, because
GameTora is one publisher and D-230 requires two. Entering the count instead of deriving it moves
where the trust sits — from the tool's arithmetic to the Trainer's reading — which is the same
trade D-270 already makes for Energy, mood and fans.

**The opening clause is bounded on purpose, because as first written it was a general principle a future round could cite for any number.** "Genuinely sourced" here means **client data or two independent sources**, not "a guide I like". Race Fatigue clears that bar on *form* (a quantified table) but not on *count*: it comes from `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` section "Gameplay Flow & Race Fatigue", **a single source**, so the table ships as a **provisional exception**, marked as such where it renders, and it is not precedent for the training-failure case — `ADR-0001` still governs there with bands. Also note the `90%+` cell: the `+` makes it a **lower bound, not a figure**, so a UI that prints "90%" states something no source claims. Either render the band as `≥90%` or keep that cell in band form.

**D-231. Race Fatigue is switched off after Late December, and a sourced 0% must silence a warning rather than shrink it.** The fatigue events *"cannot occur after Late December"*, so the advisory has a hard calendar end and must disappear in the endgame stretch, not fade. Separately and more generally: when a mechanic sets a rate to zero — Race Fatigue after December, an **Extreme Spirit Burst's 0% failure at its facility**, or a purchased **Good-Luck Charm** (40 coins, one turn) — the risk affordance goes fully quiet. Rendering "low risk" where the game says "impossible" invents a warning the player is being told to ignore.

**D-232. Trackblazer's economy is two countdowns on one strip: the shop rotation and the Grade Point deadline.** Shop: the lineup **refreshes every 6 turns** with a timer the client itself displays; a Trainer **cannot hold more than 5 copies** of one item; offers may be **Limited** (own availability window, flagged top-right) or on **Sale** (10-20% off, flagged top-left). Multi-turn items cannot be re-used while active, and buying a weaker effect after a stronger one **overwrites** it — so the order of two purchases is a real, lossy decision. Unspent coins die with the run and Climax races pay none, which makes "how long until rotation" the shop's primary number, not the balance. Grade Points: there are **four objectives** (Debut race, then 60, +300, +300, with 30/200 reductions on the Dirt-leaning aptitude track), assessed at the end of each year, and **surplus never carries forward**. So the meter must show progress against the *current* objective only and reset visibly, because a running total would imply a banking strategy that does not exist.

**D-233. The end-of-career structure is scenario-composed, like the resource strip.** URA Finale ends in an elimination progression (qualifier → semifinals → finals). Unity Cup ends after **4 Team Races** against Team Zenith and then the URA-style final races. Trackblazer replaces the bracket with the **Twinkle Star Climax**, a **3-race points league** scored on Victory Points (1st 10 · 2nd 8 · 3rd 6 · 4th 4 · 5th-6th 3 · 7th-9th 2 · 10th-13th 1 · 14th+ 0; 30 maximum) where winning the aggregate is enough and winning all three is not. A finale panel that hard-codes a three-round knockout is a URA shape imposed on a scenario that does not have one.

**D-234. Unique Skill gating is a third distinct scheme per scenario, and Trackblazer's is two-dimensional.** URA gates on fans alone at three Senior-year events. Unity Cup gates on the same fan numbers but **drops the April bond condition** because Akikawa is absent. Trackblazer replaces both with an annual **"Umamusume of the Year"** selection in Late December requiring **fans and Akikawa bond together** — uma.guide reads 5,000/19, 60,000/31, 120,000/51 while GameTora states the same gates as bond bars (blue 1, blue 2, green 3); the two agree in shape and differ in unit, so show the pair, not a false precision. No single "next gate" widget can serve all three, and Trackblazer's needs two inputs to be legible at all.

**D-235. Wiki English is not client English; normalise terminology on import, not on display.** The three new source files carry **"Wisdom"** for **Wit** and **"Motivation"** for **Mood** in their own table headings, and their item rows quote those headings directly. A third trap sits alongside them: all three say **"scenario factor"** where the client says **Spark** — `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` section "Unity Cup (GameTora)" glosses its own heading as *"Scenario Factor (Inheritance Spark)"*, which is the mapping spelled out. Their numbers are usable; their column labels are not.
This is already enforced where it matters: `gate.py`'s `TERM_BANNED` includes `\bwisdom\b`, `\bmotivation\b` and `\bfactor\b`, and it scans prototype rendered text, so a paste that imports the wiki vocabulary fails G-3. The gap was never the check, it was the assumption that a trustworthy table arrives with trustworthy headers. `Light Hello` is a separate, worse case in kind: it sat in D-210's "real Global strings" list as a **skill** while being a **Pal support card** and a character. Nothing in a source table's *row label* certifies what category the value belongs to, so an imported name needs its kind confirmed too.

### 10n. Scenario Configuration Matrix

The single table a reviewer checks a screen against. One column per scenario, three dimensions: **what appears**, **how the turn flow changes**, **what must be legible at a glance**. A cell reading *absent* is a hard requirement, not an option — rendering a widget a scenario has no mechanic for states something false about the run (D-220).

Sourced from `docs/scenarios/01`–`06`, `UMAMUSUME_REFERENCE.md`, and `scenarios.json`.

#### Dashboard layout shifts

| Widget | URA Finale | Unity Cup | Trackblazer | Our Grand Concert |
|---|---|---|---|---|
| Turn chip, trainee + scenario identity, Energy gauge, Mood tier, stat band, timeline | present | present | present | present |
| Resource strip | **baseline**: turn, trainee, scenario, Energy, Fans | + Team Rank, Spirit Burst count | + Grade Points, Shop Coins | must degrade to baseline |
| Race Calendar (goal races) | present — the scenario's spine | present, **plus** Team Race slots as a distinct slot kind | **absent** | unknown → absent until sourced |
| Grade Point meter with deadline | absent | absent | present | absent |
| Team Rank gauge (per-stat letter) | absent | present | absent | absent |
| Spirit Burst roster (per teammate) | absent | present | absent | absent |
| Team Race schedule + countdown | absent | present | absent | absent |
| Shop Coin counter + restock countdown | absent | absent | present | absent |
| Epithet route tracker | absent | absent | present | absent |
| NPC friendship bars | present (Akikawa, Otonashi) | **Akikawa absent** — Riko Kashimoto stands in; the April gate has no bond condition | present (Akikawa, bond gates Unique Skill) | unknown |
| Scenario Link identity | Aoi Kiryuin (one) | five, chosen via Team Name | **none** | five per `scenarios.json` |
| Finale panel | qualifier → semis → finals, elimination | 4 Team Races then Team Zenith, then URA-style finals | **Twinkle Star Climax: 3-race points league** | unknown |

**The fourth column is the architectural test.** Our Grand Concert is live on Global, caps Speed at 1600, and has no mechanics guide in `docs/scenarios/`. A layout that renders correctly for three known scenarios but *crashes or invents chrome* for an undescribed fourth is not scenario-aware — it is scenario-hardcoded. The required behaviour is baseline strip plus known caps, and silence about the rest.

#### Guided input flow adaptations

| Flow step | URA Finale | Unity Cup | Trackblazer |
|---|---|---|---|
| Choose activity | Training / Rest / Recreation / Race | same, but the training tile shows **which teammates are present** and how many white flames | Training / Rest / Recreation / Race, **racing is the strategy not the interruption** |
| Facility prompt | level from personal repetition | level from **team rank**; prompt must show occupancy, because multi-uma bonus scales 2/3/4/5 flames | level from repetition **plus purchased levels** |
| Extra step | none | **Team Race step**: pick 1 of 3 NPC opponents, then field 1–3 teammates per distance across 5 races | **Shop step**: spend coins; must warn that a weaker item bought after a stronger one overwrites it |
| Race prompt detail | goal + fan eligibility | goal + team-race slot; **extra racing is discouraged**, so no nudge to race | Grade Point yield + coin yield + **Race Fatigue risk from consecutive-race count** + VS flag |
| Energy at commitment | adjacent to Confirm (D-171) | same, and **no energy penalty on Special Training** (post-2026-07-01) | same; the Debut race itself costs Energy here |
| Preview honesty | success/failure branches, "Odds not shown" | same; burst hints name the **card's pool**, never the specific hint | same; fatigue may show a real % (D-230) |

#### Visual indicators

| Indicator | Scenario | Requirement |
|---|---|---|
| Spirit Burst state | Unity Cup | **six** states: chargeable → charged → held → normal spent → **Extreme chargeable** → Extreme spent. "Spent" is never terminal (D-223) |
| Extreme burst active | Unity Cup | purple, and it **silences** the facility's failure risk rather than lowering it (D-231) |
| Team Rank letter | Unity Cup | letters `G…S` **plus S+**, decoupled from facility level, which still tops out at 5 (D-222) |
| Facility level chip | all three | names its cause: repetition, team rank, or purchased (D-222) |
| Grade Point meter | Trackblazer | progress against the **next** deadline only, with a visible "surplus does not carry over" marker (D-232) |
| Shop countdown | Trackblazer | turns until rotation; plus max-5-copies holding limit, Sale flag top-left, Limited flag top-right (D-232) |
| Epithet route progress | Trackblazer | per-route won/needed count, e.g. a 3-race route at 2/3 — the routes are what shape the racing schedule |
| Race Fatigue chip | Trackblazer | consecutive-race count with its band; **must disappear after Late December** (D-230, D-231) |
| Fan readout | all | two unrelated scales never share a widget: entry gates 350–25,000 vs event gates 60k/70k/120k (D-224). Trackblazer's Unique Skill gate is **two-dimensional**: fans *and* Akikawa bond (D-234) |
| Stat cap stack | all | `1,200 base · +scenario bonus · +breakthrough · deck untracked`, per-stat denominators, never one global constant (§6.22, D-212) |
| Locked race cell | URA, Unity Cup | shows the `fans_needed` figure and distinguishes fan-lock from maiden-lock (G-16a, G-16b) |

#### Matrix rules

**D-240. Scenario configuration is data the layout reads, not branching the layout contains.** Every row above resolves from the run's scenario, and the set of scenarios is open — a fifth exists on JP and Global already has four. Implement as a per-scenario descriptor (which widgets, which slot kinds, which gates) consumed by one composable strip and one composable timeline, not as three conditionals in a template.

**D-241. An undescribed scenario must render as baseline plus known caps, never as a guess.** The matrix's fourth column is the acceptance case: our own design must be able to display a scenario it has no guide for without inventing a Shop panel or a Team Rank gauge. Absence is the safe state, and it is also the honest one.

**D-242. Widget presence and widget meaning both change per scenario, so a shared widget must carry its cause.** A Level 5, a fan count, and a "race" prompt each mean something different depending on the scenario. Rendering the same glyph with a different underlying rule and no label is the failure this matrix exists to prevent.

### 10o. Mockup review findings — v9 scenario-aware round

Four mockups were generated against §10n and then read back at full resolution, because D-184 says a PNG cannot be gate-verified and every prior round proved it: image generation invents plausible labels. Twelve findings, five fixed by regeneration, seven recorded as rules.

#### Fixed in the v9b regeneration

| # | Defect | Fix |
|---|---|---|
| F1 | URA skill list contained **"Endurance Up"** — `endurance` is banned by `gate.py`'s `TERM_BANNED` (→ Stamina). A banned word arrived inside a *fabricated skill name*, so no existing check could have caught the name, only the word | Replaced with four evidenced strings from D-210: Homestretch Haste, Unstoppable, Gourmand, Up-Tempo |
| F2 | Unity Cup read "Power facility Lv 4 ← team rank S" and "Wit facility Lv 4 ← team rank S". Rank **S yields Lv 5**. The one widget whose entire purpose is to state the cause of a level stated it wrongly | Corrected, and the legend `G F → 1 · D E → 2 · B C → 3 · A → 4 · S → 5` now printed beside it |
| F3 | The same teammate was slotted into two distance rows at once (Rice Shower in Mile *and* Long) | One member per distance enforced |
| F4 | **Heart glyph did double duty** for Stamina and Energy, both green, both in the header band | Energy is now a rounded battery glyph; heart belongs to Stamina alone |
| F5 | Advisory bubble carried monogram **"TK"**, which maps to no NPC in any scenario | "AK" for Aoi Kiryuin, the URA Scenario Link |

#### Recorded as rules

**D-250. Bar fill is one colour; only the header tint varies per stat.** The URA frame rendered all five stat bars in the neutral lime fill; the Unity Cup frame rendered them blue, pink, green, orange and purple. Both were produced from prompts describing the same system, and they disagree. D-114 already bans saturated stat identity hues, and the §3.6 tints exist precisely so stat colour can be *suggested* without being *shouted*. A per-stat bar fill re-introduces the withdrawn five-colour scheme through the back door and makes the two frames read as two different products.

**D-251. A glyph may carry one meaning across the whole system, and the flame currently carries three.** Guts is a flame, Spirit Burst "chargeable" is a flame, and facility occupancy is a cluster of flames. The occupancy flames are defensible — they *are* burst fuel — but Guts-as-flame makes the stat band and the burst roster speak the same symbol for different things. Pick a distinct Guts glyph, or a distinct burst glyph, and record the choice in §6.0.

**D-252. Never render a per-flame gain as a constant.** The Unity Cup facility rows all read "+3 primary / +1 secondary". Those numbers are a function of flame count *and* facility — the sourced table gives Wit at 2 flames as +1 Wit, +0 Speed, 0 SP, and 5 flames as +6 Wit, +2 Speed, 6 SP, while Speed at 2 flames is +2 primary, +0 secondary, 1 SP. A uniform figure is invented filler sitting in the exact place the tool's most valuable number belongs, and it is the kind of plausible constant that survives review until someone checks it against the table.

**D-253. `Suggested` must not be blue.** The URA skill list rendered the `Suggested` chip in blue outline, and blue is the **loss** colour under D-133. A suggested skill is not a negative event. The three acquisition states need a palette of their own that does not borrow from gain/loss semantics.

**D-254. Navigation labels are scenario-composed too.** The Trackblazer guided-input frame's dimmed background still showed a nav item reading **CALENDAR**, in the one scenario that has no race calendar (D-221). Removing the panel while leaving its name in the rail states the falsehood the rule exists to prevent, and it is easy to miss because the offending element sits in the background layer of a composition about something else.

**D-255. Sample state must vary, and must be able to be wrong.** All six support-card slots rendered a **full** bond meter, including the R card. Bond is the resource three URA event gates and Trackblazer's Unique Skill gates actually read, so a mockup where it is uniformly maxed depicts the one state where no gate ever fails. Same for the invented training activity names "Hill Repeat" and "Easy Run" in the Screen B background — D-76 requires sample data to be real Global strings, and an unverified activity name is a fabricated client string in a place nobody thinks to check.

#### What the round demonstrated about the process

Every factual defect above was caught by a human reading the rendered image at full resolution, and none would have been caught by `gate.py` — it scans HTML prototypes, not PNGs. F1 is the sharpest case: the banned word `endurance` *is* in the gate's list, so had that skill name been pasted into a prototype the gate would have failed it, but as a mockup label it was invisible to every automated check. **The mockups are art direction, not evidence.** Any string that must be correct belongs in an HTML prototype where G-1, G-2, G-3 and G-13 can reach it.

### 10p. Convergence and derivation rules

Recorded after the v10 build, which replaced the PNG Screen A set with a single HTML artifact so the gate could reach the labels.

**D-256. A derived value must print its own boundaries, and must say whose derivation it is.** The grade badge is the sharpest case in the system: it is a single letter presented with total confidence, and nothing in this repository defines the scale it comes from. `screen-a-scenario-v10.html` computes the letter from the entered value through a 150-point banding and prints the full table beside it under a `[Provisional]` marker. That is the minimum acceptable shape for any derived figure: the number, the rule that produced it, and whether the rule is the client's or ours. A letter with no visible derivation is a fabricated client string wearing a badge, and it fails Planner Rule 5 as well as D-20.

**D-257. Converge to one layout spine; let scenario composition carry the difference.** The dashboard and the guided input are one vertical spine, and the only region that varies is the goal panel set. Three consequences worth stating because each cost something to give up:
- The per-scenario anchors from the v9 mockups (top-left lead, off-grid editorial, centred-low) are dropped. Variety across screens was the more attractive design and the worse product: three anchors read as three applications, and a Trainer moving between scenarios loses every positional cue.
- The two guided-input variations are dropped in favour of the inline preview. The side column separated a choice from its consequence and had already shipped a defect where the preview rendered above the choices.
- Retired artifacts move to `prototypes/superseded/` with a README naming their replacement, rather than being deleted. `gate.py` sweeps `prototypes/*.html`, so the subfolder also stops the gate from certifying files that contradict the current system. Three of the four retired files rendered stat band headers that are not the §3.6 tint system at all, one of them white on `#106F9F`.

**D-258. A contrast rule must name its second colour.** "1.04 to 1.14 against the panel" is not checkable while the component is painted on `--raised`. The dark tint set was solved against the surface it sits on and measures 1.09 to 1.11; the light set was solved against `--panel` and measures 1.10 to 1.21 where it actually renders. Same rule, two reference surfaces, one of them quietly failing. Every contrast threshold in this file must state both colours, or it cannot be verified by anyone, including the author.

**D-259. A mood pill renders the directional arrow. Colour plus word without it is a review failure.** The five tiers are `GREAT` ↑ +20%, `GOOD` ↑ +10%, `NORMAL` → 0%, `BAD` ↓ −10%, `AWFUL` ↓ −20% (`DESIGN.md` §6.17, from the client's own Mood Effect panel), and the arrow is not ornament: it is the only part of the readout that carries the ordinal direction. The middle tier's mark is read from the capture as neutral, and whether the client draws a flat arrow there or nothing at all is the one glyph worth re-confirming on the next capture; the rule is unaffected, since the tiers either side of it are unambiguous.

The reason it is now a rule rather than a preference is arithmetic. Three of the five pill colours are derived, not measured, and were derived at the mean relative luminance of the two measured anchors per the owner's fallback instruction (`DESIGN.md` §3.5). At equal luminance the ramp's own structure collapses: `GOOD` `#ED8036` and `BAD` `#D48556` are 3.3 degrees of hue apart at the same brightness, and `GREAT` `#FB5590` and `AWFUL` `#D47E9E` are 1.0 degree apart. Two pairs of *opposite* meaning therefore render as one colour, and no amount of re-picking saturation fixes that while the luminance is held equal. What the equal-luminance derivation buys is a row of five pills of even weight, which is what was asked for; what it costs is hue as an ordering signal, and the arrow is what pays for it.

Consequences, all checkable:
- The arrow renders on every mood pill, at every size, in every theme, including the compact forms in the log and the guided preview.
- The arrow is the client's own grammar (up for the two positive tiers, flat at `NORMAL`, down for the two negative ones), so it is a measured pattern rather than an invention, and it must not be replaced by a sign-prefixed number or a colour-only chip.
- G-6 (colour independence) is necessary but not sufficient for a mood pill: G-6 passes on a word-only pill, and a word-only pill still fails here. G-57 is the specific check.
- When the three provisional colours are replaced by measured ones, this rule does **not** lapse. The arrow is a client behaviour being copied, not a compensation this system chose.

### 10q. Legacy Select rules

From seven client frames dated 2026-07-15 and the Global Legacy and Sparks guides. Screen
anatomy in `DESIGN.md` §6.27.

**D-260. Legacy Select is a pre-run step, never a per-turn one.** The client reaches it before training begins: create run, choose scenario, choose Trainee Umamusume, choose Legacies, then turn one. It must not appear in the guided turn flow, must not be reachable from a turn, and its result is fixed for the life of the run. A per-turn Legacy control would imply the Trainer can re-choose ancestors mid-run, which the game does not allow and which would silently invalidate every turn entered before the change.

**D-261. Every Spark names the Legacy it came from and its generation.** The right-hand list groups under `1st Legacy` and `2nd Legacy`, each headed by the ancestor's portrait and rank badge, and second-generation Sparks carry half the inheritance rate. So the group heading is not decoration, it is the provenance: the same `Speed ★2` means different odds depending on which group it sits in. A flattened Spark list that drops the grouping throws away the one fact that changes the number's meaning (Planner Rule 5).

**D-262. Affinity is entered or fetched, never inferred.** As written this rule asked that affinity "be deterministic and explainable". That is not achievable here, and stating it unchanged would license a guess. The client derives affinity from shared competitive history, principally overlapping G1 wins between the Trainee and the Legacy, plus canonical relationships. **The schema stores none of that**: `training_runs` has two nullable FKs to `umamusume` and no per-character race record, so no function over local data can produce an affinity value. The honest form is therefore: the Trainer enters the affinity the client showed them, or it arrives from a fetched dataset with provenance, and the UI displays it as recorded. It is explainable precisely because it is not computed. Any proposal to compute it is an Architect escalation and almost certainly a PRD §6 non-goal.

**D-263. A Guest Legacy is read-only, and absent beats disabled.** A borrowed friend Umamusume carries a `Guest` pill over its portrait. The Trainer cannot alter another player's Umamusume, so the controls that would mutate it are not rendered at all rather than greyed out. A disabled control invites a click and then refuses it; absence states the fact up front (D-26 family). Borrowing costs are the game's economy, not this tool's: record that the Legacy was a guest, never a Monies figure.

**D-264. Inheritance is a roll. Show the published chance and the observed result, never a projection.** The client's rates are public: stat Sparks 70 / 80 / 90% at ★1 / ★2 / ★3, aptitude Sparks 1 / 3 / 5%, Unique Skill Sparks 5 / 10 / 15%, and half again for second-generation Sparks, with affinity applied as a percentage multiplier. A row may read `Stat spark, 90% chance`, because that is a sourced constant. It may not read `likely to pass Speed`, because that is a guess about a random event, and Planner Rule 1 forbids simulation while Rule 4 requires every number to be a pure function of entered turns. This is the sharpest boundary on this screen: it is the one place in the product where the underlying game is explicitly random, and the temptation to model it is constant.

**D-265. Spark category colours are contained to the Sparks list.** Measured fills: stat `#3CB4F0`, aptitude `#FC84B4`, Unique Skill `#90CC30`. These mean *category*, and they collide on sight with §3.3, where blue already means "this stat decreased". The containment rule: no delta, badge, status pill or chart may reuse these three fills, and the Sparks list is the only surface where they appear. They also never appear without their word and their star count, so the colour is a scanning aid rather than the carrier of meaning (P5, D-12).

> **Withdrawn mapping (2026-09-29, R83).** While deciding how a Legacy Select form would label the Spark
> kinds, a mapping was put to the owner that paired **aptitude with `#90CC30` green and Unique Skill with
> `#FC84B4` pink** — the two fills transposed. It was approved in that form and is **withdrawn**: this
> rule's measurements are the authority, and they read stat `#3CB4F0` blue, aptitude `#FC84B4` pink,
> Unique Skill `#90CC30` green. The transposed mapping reached no tracked file — `git grep` over the
> three fills returns only this rule, `DESIGN.md` §6's kind table and `MECHANICS-TRANSLATION-TRIAGE.md`,
> all three already correct — so this note records the withdrawal where the fills live rather than
> correcting a shipped line. It is filed here because D-265 is what a future reader checks the fills
> against, and an approved-but-wrong mapping that leaves no trace is the one correction that cannot be
> found by grepping.

**D-266. Reproduce the client's own inconsistency rather than normalising it.** The slots read `Legacy 1` / `Legacy 2`; the Spark groups read `1st Legacy` / `2nd Legacy`. Two numbering styles for the same two objects in one screen. It is the client's inconsistency, not ours, and a Trainer reconciling the tool against the game needs the strings to match what they see. Invent a third form and the mapping breaks; silently unify the two and the labels no longer correspond to anything on screen.

**D-267. The "parent" ban cannot be applied to the schema in a UI phase.** UI copy uses Legacy and Ancestor throughout, which is free. But the shipped columns are `inheritance_parent_a_id` and `inheritance_parent_b_id`, the relations are `inheritanceParentA()` and `inheritanceParentB()`, and Game8's Global guide itself glosses Legacies as "parents". Renaming columns is a migration with a PRD citation, out of a documentation and mockup phase. Recorded as a known divergence between the copy rule and the identifier rule, for the phase that owns the schema to settle.

**D-268. The screen has more state than the schema can hold.** Legacy Select shows, per Legacy: the chosen Umamusume, its rank, whether it is a Guest, its own two ancestors, and a Spark list with per-Spark kind, target and star rank, plus an affinity value for the pairing. The `training_runs` record holds two character ids. Phase 4's provenance requirement is therefore **not met by the existing columns**, and closing it is a schema proposal on the `ADR-0003` pattern, most likely a typed json payload keyed to the run rather than a wide table of nullable columns. Documenting the screen without stating this would imply the data can be persisted today.

**Closed as a schema question 2026-09-29 (Slice 15, `ADR-0010`), and it is still open as a screen.** `training_runs.legacy_selection` is now one nullable json column read through `App\Models\Legacy\LegacySelectionPayload`, holding exactly the state enumerated above, with the two `inheritance_parent_*_id` foreign keys left where they are. Three things this does **not** do, so a reader of the paragraph above does not infer them: it builds no Legacy Select UI (D-260 still governs where a control may appear once one exists); it computes nothing from the payload, which is the half PRD §6 non-goal 3 keeps banned; and it stores one affinity grade for the chosen pair rather than the per-link grades §1.5.4 actually grades, so a screen that shows the four deeper links of the diagram needs the column revisited. `ancestors` holds names, not ids, because the Umamusume two steps back are frequently absent from the local catalogue.

### 10r. Run Completion state rules

Note on numbering: this was requested as "10p", but §10p is already Convergence and derivation
rules and §10q is Legacy Select, so it continues the sequence as §10r. Component anatomy lives
in `DESIGN.md` §6.28, not here, because §10x in this file is for rules and DESIGN §6 is for
anatomy.

**D-270. Run Completion is a terminal, read-only summary, and its headline number is entered, not computed.** Career Rank and Rating are the client's own score of a finished run, derived from final stats, race results and fans across the whole career. Recomputing them here would be a simulation and is barred by Planner Rule 1; Rule 4 requires every displayed number to be a pure function of entered turns, and the rating is not. So the Trainer records what the client showed, the UI labels it as entered, and an absent rating renders as absent rather than as zero or as an estimate.

**D-271. A Major Win may only be rendered if its source is real data.** Single races must exist in the scenario calendar or race dataset. Composite achievements such as `Senior Autumn Triple Crown` and `Triple Tiara` are not races, have no row in a race table, and must come from a curated title list. The failure this prevents is the one this project has hit repeatedly: a plausible-sounding string generated into a surface that looks sourced. A gold medal icon and a blue tier badge also encode different kinds of achievement and must not be interchanged.

**D-272. The fan class ladder is a fourth, separate fan scale and may not be merged with the others.** Measured thresholds: Bronze 5,000, Silver 20,000, Gold 50,000, Platinum 100,000, Star 160,000, Top Star 240,000, Legend 320,000, over a Debut/Maiden and Beginner base. These are distinct from race entry gates (350 to 25,000), URA event gates (60,000 / 70,000 / 120,000) and Trackblazer's Unique Skill gates (5,000 / 60,000 / 120,000 with bond). D-224 already forbade merging two of these; the ladder is a fourth and the same reasoning holds. It is also the scale that matters at run end, so the summary must name the tier reached and mark it, not leave a bare fan count to be looked up.

**D-273. Grade badge palette, corrected from frames.** `A` orange, `B`/`B+` pink, `C` green, `D` blue, `F` periwinkle, `G` grey, `S` gold. An earlier draft grouped `F` with `G` as grey; the client renders F in the same violet-blue seen on the Legacy Select frames, and greying it destroys the distinction that makes the ladder scannable. The letter always accompanies the colour regardless (P5, D-12).

**D-274. The `U/G` badge must not be rendered, mapped, or explained until it is understood.** Speed 1245 on a finished run carries a large violet `U` with a smaller grey `G` at its lower right, which is not a member of the G-to-SS ladder. Its meaning is unestablished. The temptation is to treat it as the top tier and assign it a threshold; that would promote an uninterpreted glyph into a rule and silently corrupt the grade function above roughly 1200, which is exactly the range a completed run lives in. Absent beats invented.

**D-275. The race tier set is not closed, because `EX` exists.** Pre-OP, OP, G3, G2 and G1 are the five this package had recorded; the Climax result header shows an `EX` badge, and the same badge appears on the Legacy Select frames, so it is a general class marker. This is direct evidence for D-153's ban on deriving a tier label from a grade code: an unseen code would be silently mislabelled rather than flagged.

**D-276. The final stats list is mandatory and the radar chart is optional.** The client offers a swap between them. The list carries values and grade badges; the pentagon shows shape without magnitude, so a run can be reviewed without it but not without the numerals. A chart that is the only carrier of a number is a chart placed because the space looked bare (D-94).

---

### 10s. Support card rules

Recorded from the deck-editor frame `Screenshot 2026-07-15 155016.png`, the six card detail panels
captured the same minute, and the export decode in `UMAMUSUME_REFERENCE.md` §1.4.7. These describe the
client, so they hold whichever way `ADR-0005`'s scope question is answered.

**D-277. `Friends` names the sixth slot, never the card in it.** The client frames slot six in pink and
captions it `Friends` under the level, and the captured deck puts a `stamina` card there. So the caption
is a slot role and the type chip on that same card still reads heart. A design artifact, mockup or schema
that treats "friend" as a property of the card is wrong in a way that is invisible until a Trainer puts a
stat card in slot six and the app calls it a Pal. The export's `type: friend` is a separate, real thing —
the 23 NPC staff cards — and the two must not be merged (D-20, G-38).

**D-278. The client's own word for the sixth type is still not on record.** "Pal" comes from Game8's
list, not from a captured string, and the export key is `friend`. Render the discipline names the client
uses — Speed, Stamina, Power, Guts, **Wit** — and store the export key `intelligence` behind that word at
the model boundary. For the two special types, label them from a capture or mark the label
`[Unverified]`; do not ship "Pal" as though the client said it.

**D-279. Effect magnitudes are interpolated, and the interpolation is shown.** The client's value at any
level is the **floor** of a straight line between the stored anchor levels (1, 5, 10, 15, 20, 25, 30, 35,
40, 45, 50), verified against the client at levels 30, 35 and 40 on five effects at once. Two obligations
follow: truncation, not round-half, because the client truncates and a one-off figure reads as a data
error; and the anchors the value was drawn between print beside it, per D-256. A materialised value-per-
level table is forbidden — it is 50 rows of derived data per effect that can disagree with the rule that
generated it.

**D-280. Never display a Unique Perk magnitude.** The perk has its own level axis, independent of the
card's (`Lvl 50 / 50` with perk `Lvl 30`; `Lvl 35` with perk `Lvl 40`), and no source in this repository
carries its values — for `[Dream Big!]` Tokai Teio one of the two named perk effects is not even present
in that card's effect list. So a perk block may show the name, and the level only where the Trainer
entered it, and nothing else. A percentage under a perk heading is an invented statistic (D-20, D-256).

**D-281. `Scenario Link` is derived from the scenario, never stored on the card.** The badge appears on
exactly those cards whose character is on the running scenario's linked list; in the captured Unity Cup
deck one card of six carries it and it is the deck's only linked character. A stored flag would freeze a
per-scenario fact into the card and would then be wrong in the other three scenarios, which is the same
class of error D-227 warns about for unqualified numbers.

**D-282. The slot's four corners are the contract.** Rarity ribbon top-left, type chip top-right, four
limit-break diamonds bottom-left, `Lvl N` bottom-right, with the friend-slot caption below that. The
diamond count is four because a card takes four breaks, and the level readout is
`base(rarity) + 5 × breaks` (bases 20 / 25 / 30), so a slot showing three filled diamonds and `Lvl 50` is
a defect, not a style choice. The type legend under the grid prints `xN` only for non-zero counts, and
that absence is the read a Trainer is actually after.

---

### 10t. Ingested third-party design prose

Numbering continues from §10s. The full triage of the incoming document is in
`MECHANICS-TRANSLATION-TRIAGE.md`; this section carries only the rule that outlives it.

**D-283. A design write-up is a source of patterns, never of values, copy, or vocabulary.** An external "game mechanics translated into UI/UX design language" document arrived with fifteen sections, correct shape, and mostly unusable content: it uses **Factor** where the Global client says **Spark** and **Motivation** where it says **Mood** as its primary vocabulary rather than incidentally; it asserts "typically 70+ turns", which is the D-136 defect with a larger number; it claims "Speed > 2000 unlocks Full Spurt" against its own §4.2 statement that 2000 *is* the hard ceiling; and three of its sections describe systems with no Global row in `scenarios.json`. The disposition is therefore per-axis, not accept/reject: the **pattern** survives translation ("a progress-gated unlock with a visible threshold"), the **number** attached to it does not, and the **copy** never does. This is not a new obligation — it is D-227's provenance requirement and D-256's derived-value rule applied one stage earlier, to the moment a figure enters a design doc rather than the moment it renders. The failure it prevents is the one this repository has been catching all session: a plausible constant arriving from prose, being seeded, then appearing on a surface that looks sourced.

**D-284. Adopting a pattern from prose requires an existing evidence anchor.** A pattern that only the write-up supports stays in `MECHANICS-TRANSLATION-TRIAGE.md`; a pattern that this package can already point at in a capture, a dataset field, or a measured frame is the one worth naming in `DESIGN.md`. Both of the adoptions made from this document pass that test — informed risk-taking is already implemented as the preview step and D-171's energy-beside-Confirm rule, and behaviour gates are already drawn as §6.5's second marker — and the ones that fail it, like support card anatomy, stay out of the design system regardless of how good the writing is.

**D-285. A document that cites this repository is a mirror, not corroboration.** A second incoming write-up (`docs/UMAMUSUME PRETTY DERBY — COMPREHENSIVE UX DELIVERABLES.md`) states at line 5 that its numbers are "drawn from the source-cited reference guide (compiled 2026-09-27)", and it is telling the truth: its Trackblazer cap row reproduces `UMAMUSUME_REFERENCE.md` §1.3.4's scenario-cap row — the cell now carrying the "Climax row corrected 2026-09-27" note — **verbatim, including the Guts/Wit transposition that `SCENARIO-DIFFERENCES.md` diagnosed**, and its mood table reproduces the `❌ UNVERIFIED` status that §1.1.6 and `ADR-0001` §6 both closed earlier the same day. Two consequences, and the second is the uncomfortable one. First, two artifacts agreeing is evidence of a shared source, never of the source being right — so a third-party doc that matches our reference cannot be cited back at ourselves. Second, when an incoming artifact disagrees with an authority here, check the authority's *internal* consistency before concluding the outsider invented the figure; in both cases here the "invention" was our own stale row, still circulating because the correction had been applied at one location and not the other. The corrected §1.3.4 cap row now names this risk in its own source cell.

**D-286. A correction is not finished until every copy is gone.** Changing a value in one table while it stands corrected-elsewhere-and-wrong-here is the defect that produced D-285, and it is invisible to review: the file you edited reads correctly. The unit of correction is the literal, not the file. This applies hardest to figures that mockups, seeds, configs and other documents quote — the cap rows and the mood percentages are both in that class. The same rule cuts the other way for deliberate historical text: `ADR-0001` §6 keeps the superseded glosses below a dated "RESOLVED" block, and `DESIGN.md` §6.22 records the pre-correction row as a withdrawn instruction. Those are citations of an error, not copies of it, and G-60's grep is read with that distinction, not applied blind. **A third category exists and is exempt:** the inert one-shot patch scripts under `docs/design-research/_scratch/`, which are how several of these literals were written into the corpus in the first place. They are provenance records, not live text, and they will keep matching every retired string forever — so G-60 counts hits in `docs/**` prose, `config/**`, `resources/**` and prototypes, and treats `_scratch/` as history unless a script is being re-run, in which case its literal must be updated before it executes.

**D-287. A correct rule with a wrong reason still fails review.** Two findings in this repository were of that shape, and both survived every check that looks at the rule rather than its citation. D-211's halved-gains rule is right and its source was misattributed — "The URA guide states plainly", in a file containing neither the word *halved* nor the figure *1200*; the sentence is from Game8's `[Global]` English pages, as `ADR-0002` amendment 1 already recorded. The same rule also credited `Fully Charged` to URA when it is an Our Grand Concert mechanic. D-187's banner rule is right and its stated evidence was an over-reading — six activity names, all from Unity Cup frames, offered as proof that layouts differ *between* scenarios in a corpus with zero Trackblazer training HUDs. Both defects had been copied outward before they were caught: D-211's into `CONSTRAINTS.md`, D-187's into three files. So review asks two questions of every rule, in this order: **is the requirement true, and does the reason given hold up to the evidence it names?** A rule that passes the first and fails the second will still mislead the next implementer, because the reason is what they generalise from.

**D-288. Theme tokens are declared `@theme static`, because Tailwind prunes what nothing references.** Tailwind v4 emits a `@theme` custom property only if some generated utility uses it. A token added for runtime theming but not yet referenced by a class is dropped from the stylesheet, and `var(--color-x)` then resolves to nothing — so the property inherits the element's existing value instead of failing. The practical danger is not a broken render, it is a **silent false pass**: a contrast probe on an undefined token measures whatever colour the probe element already had and reports a green result. Observed 2026-09-27 while verifying the light-first flip — `--color-sp-ink` was present in the dark override and pruned from the default theme, and a naive check scored it 6.56:1 against its real 5.05:1.

Two obligations follow. The declaration block is `@theme static` so every token ships whether or not a class has adopted it yet, and a contrast gate must read the **resolved property value** (`getComputedStyle(root).getPropertyValue`) and fail loudly on empty or unparsable, never sample through an element that already has a colour. Cost of `static`: 0.03 kB in this project's bundle. A third-party write-up that describes this as "just use CSS variables" is describing the failure, not a fix.

**D-289. A specification clause is a claim about the implementation, and it ages like one.** A line of `DESIGN.md`, or a note in a corpus file, that states a treatment, a value, a token, or what a source carries is a claim — not decoration — and it is worth only as much as the last read of the tree it describes.

Five have been caught this run by someone opening the file instead of the doc, and **two are still standing** — which is the honest shape of the problem and the reason the rule is written down rather than settled.

**Standing.** First, `docs/scenarios/09-global-race-calendar.md:59` states *"the map is trusted, the per-row assignment is not independently audited"* — the position R75 retired on 2026-09-29 by nullifying 115 per-row tier labels, in a file that never received the correction. Second, `09`'s Known-gaps bullet *"Per-character Goal races are not in the export… no GameTora key carries them"* is falsified by `ura-objectives`, which is in the manifest the file never enumerated; the dated withdrawal exists as a draft and has not landed, so the false sentence is still the one a reader finds.

**Caught and closed.** First, and the strongest case in the set, because the rule caught it in its own draft: **`D-285` — the rule saying a document that cites this repository is a mirror, not corroboration — carried a citation rotted by exactly the mechanism D-289 is about**, pointing at the Trackblazer cap row as "§1.3.4 line 357" while the corrected row had slid thirteen lines to `:370`. A rule about citation chains whose own citation is one link behind the tree it names is not a counterexample; it is the demonstration, and the demonstration is worth more kept as a dated erratum than kept as a live broken pointer — leaving it patched-never would have made the exception that lets a rule dodge itself. Corrected to cite the row by its heading and its own "Climax row corrected 2026-09-27" note. Second, `skills` was described as ten seeded names until `ADR-0011` measured the import at 1,910 rows with 623 reaching a Trainer. Third, two clauses in one paragraph of the root `DESIGN.md` §4.2 — "dateless rows show 'Unknown'", which turned `CLAUDE.md:23`'s **storage** rule ("no sentinel dates … use nullable date + `release_status`") into display copy that exists on no other surface, and "JapanOnly renders the amber notice", a treatment the token set cannot produce — were rewritten alongside the KI-35 filing (`bcd8abe`), both clauses in one dated withdrawal because they were written together and read together.

The clause about line numbers is not pedantry, and the case that pays for it is the rule's own: **a clause that cites a location should cite a section heading or a name, not a line number.** Line numbers are the fastest-decaying fact in a document anyone edits — `git log --since=2026-09-26 -- docs/UMAMUSUME_REFERENCE.md` shows six commits through that file since the cap row was corrected, which is how `D-285`'s pointer slid from `357` to `:370` without anyone touching `D-285`. **And the corpus owes the same to itself: any slice that edits a file with section-anchored references checks that the anchors still resolve, because the alternative is a rule that quietly stops pointing at anything.** `D-285`'s corrected citation is the demonstration — a rule about a citation chain whose own citation had fallen one link behind the tree it names, caught while the rule about it was being drafted. Anchoring by section or by name at least fails visibly, to a heading a reader can search for, rather than silently, to a line that still exists and says something else.

The obligation therefore runs in both directions, and this is the half that costs a reviewer something. D-285 asks that an incoming document not be cited back at ourselves; **D-289 asks that our own clauses not be cited at all without being re-derived against the tree first.** Any review that leans on a specification line as authority re-derives it, and a clause found stale is corrected by that review rather than carried forward — the correction lands in the same commit as the work that needed it, which is how §4.2 and KI-35 arrived together. It is the same discipline D-283 and D-284 apply to a write-up that arrives from outside, and D-287 applies to a rule's stated reason; what it adds is that the corpus owes it to itself, on a schedule, in the ordinary course of reading.

**The gate that cannot cover this.** G-60 greps *retired literals*, so it fires when a wrong value survives an edit; it has nothing to check when the value is right and the sentence about it goes stale, because no literal changed. That gap is the reason this is a review obligation and not a script.

---

### 11. Verification gates

These are the checks a reviewer runs on any UI change. They are additive to the root file's sequence.

**Scripted versus manual, so no gate reads as stronger than it is.** This table is a checklist, not a report of automation, and the distinction matters: a reviewer who assumes G-60 runs itself will repeat the exact defect it describes.

- **Enforced by `gate.py`** over the prototype HTML: **G-1** lore, **G-2** required forms, **G-3** terminology, **G-4** token discipline, **G-13** rendered-text sweep, **G-16** sample-data integrity, **G-27** `node --check`, **G-31** total-turn denominator, **G-32** cap denominator honesty, plus the D-79 em dash and D-84 emoji checks.
- **Manual or browser-assisted, no automation:** everything from **G-33 through G-60**, and the rows `gate.py` itself lists as not machine-checkable — **G-5** contrast, **G-6**, **G-7**, **G-9**, **G-11**, **G-12**, **G-14**, **G-15**, **G-17**. Contrast and computed style are measured in a browser by an agent, not by the script.

G-60 is the case worth naming: it is the check that would have caught the transposed cap row surviving one file while another was corrected, and it currently runs only when a person remembers to run it. Automating it needs a registry of retired literals, so that is offered as a decision, not assumed. Two side observations from writing this: `gate.py`'s own PASS banner under-reports its coverage (it omits G-27/G-31/G-32, which it does enforce), and this very bullet was first written from grep fragments with the manual and automated lists swapped — caught only by reading the file, which is D-287's point about checking the reason rather than the rule.

| # | Gate | How it is checked | Pass |
|---|---|---|---|
| G-1 | Lore | `make lore`, then the §3.1 pattern list over views, lang files, seeders, factories, exports, and prototype HTML | zero unexplained hits after context review |
| G-2 | Required forms | grep for `umamusumes`, `Uma Musumes`, and animal collectives | zero hits |
| G-3 | Terminology | grep the §4 banned-alternative column | zero hits in UI-facing strings |
| G-4 | Token discipline | grep views and CSS for hex literals and arbitrary-value utilities | zero outside the theme block |
| G-5 | Contrast | recompute every new text/background pair | AA at size, recorded in `DESIGN.md` §3.4 |
| G-6 | Colour independence | audit each state chip for a word or glyph | every state readable without hue |
| G-7 | UI states | render empty, loading, populated, error for every data view | all four present (root C-7) |
| G-8 | Stat bounds | input 1201 and 0 and a duplicate turn number | rejected with the bound named (D-31, D-32) |
| G-9 | Provenance | inspect any catalog field on a detail view | source URL and fetched date visible (D-33) |
| G-10 | Scope fence | grep the design artifacts for prediction, snapshot, sim, upload, sign-in | zero (D-36, D-37) |
| G-11 | Keyboard | complete a guided turn entry with no pointer | full flow operable (D-55) |
| G-12 | Reduced motion | toggle the OS setting, repeat the flow | legible, no long transitions |
| G-13 | Rendered-text sweep | assert visible text for `undefined`, `NaN`, `[object Object]`, and placeholder copy | zero hits |
| G-14 | Dose caps | count saturated hues, crimson elements, gold selections, glows per screen | within `DESIGN.md` §7.1 (D-82) |
| G-15 | Control honesty | click or tab through every interactive element in a prototype | works, or visibly marked static (D-81) |
| G-16 | Sample-data integrity | list every trainee and skill name in an artifact | all real Global strings, marked as sample (D-76) |
| G-28 | Lock-state distinctness | render a fan-locked cell beside a maiden-locked cell | distinguishable without reading the text (D-173) |
| G-29 | Conditional race step | advance a turn whose calendar slot is empty | no race step appears (D-175) |
| G-30 | Energy at commitment | measure the distance from the gauge to the Confirm control in the guided flow | gauge is adjacent, not header-only (D-171) |
| G-15a | Scenario legibility | open a run in each of the four Global scenarios | the header names the scenario and the bar scale visibly changes (D-160, D-161) |
| G-15b | Cap completeness | inspect any displayed cap | the stack shows what is included and what is untracked (D-162) |
| G-15c | Bound honesty | enter a stat of 1350 | the rejection names the tool's limitation, not a game ceiling (D-31) |
| G-16a | Fan lock legibility | inspect a locked race cell | the `fans_needed` figure is shown, not a padlock alone (D-151) |
| G-16b | Lock kinds | inspect a maiden-gated race and a fan-gated race | they are visually distinct (D-152) |
| G-16c | Tier inference | grep for grade-code to tier-label mapping | none in `database/seeders/` or `config/` since R72: the seeder joins per race on the dated extraction, so it holds no code-to-label constant at all. One known residue, held as a documented dissent rather than a pass: `GametoraRaceCatalogParser.php` still carries a five-entry map, unreconciled under R72 because the coordination condition needs the peer session idle, not merely its file clean. See D-153's dated exceptions |
| G-16d | Race outcome projection | inspect the race selection panel | eligibility only, no placing or win estimate (D-155) |
| G-17a | Event overlay | trigger an event panel and inspect what is behind it | scene, turn chip and stat band still visible (D-131) |
| G-17b | Delta colour | inspect every gain and loss in an event preview | gains orange, losses blue, no green or red (D-133) |
| G-17c | Run length assumption | grep views and JS for a total-turn constant | zero (D-136) |
| G-17 | Chart justification | for each chart, read its title | it is a question the chart answers (D-94) |
| G-31 | Run length assumption | grep views and JS for a total-turn denominator | zero — the brief's "~50 rounds" is unsourced, so `/ 48` asserts a fact (D-136) |
| G-32 | Cap denominator honesty | grep for a `/1000` stat ceiling | zero, unless a sourced scenario cap says so. An invented round number is not a cap (D-212) |
| G-27 | Script parses | `node --check` every inline script in each prototype | all parse. A static text scan passes happily on a page whose JS is broken, so the interaction stays invisible until a browser is opened (D-184) |
| G-33 | Scenario composition | render the same run header in all four Global scenarios | each shows only the resources that scenario actually has, and URA's strip is visibly the sparsest (D-220) |
| G-34 | Calendar applicability | open a Trackblazer run | no Race Calendar panel, and Grade Point progress appears in its place with a deadline and a no-carry-over note (D-221) |
| G-35 | Level provenance | inspect a Level 5 chip in each scenario | it names its cause: repetition, team rank, or a purchased level (D-222) |
| G-36 | Burst state completeness | advance a Unity Cup teammate through normal then Extreme burst | six distinct states render, "spent" is never terminal, and no energy penalty appears on Special Training (D-223) |
| G-37 | Zero-rate silence | trigger a sourced 0% risk (Extreme Burst facility, Race Fatigue after Late Dec, Good-Luck Charm) | the warning is removed entirely, not downgraded to "low" (D-231) |
| G-38 | Imported label check | grep artifacts sourced from wiki tables for `Wisdom` and `Motivation` | zero — both must read Wit and Mood (D-235) |
| G-39 | Source currency | for each seeded numeric, read its source's date | it postdates the 2026-07-01 Global rework, or the value is marked provisional (D-228) |
| G-40 | Matrix conformance | for each screen, read the §10n row matching its scenario | every widget present on the screen is required by that column, and every *absent* cell is genuinely absent (D-240) |
| G-41 | Undescribed scenario | render a run in a scenario with no guide and no fixture data | baseline strip plus known caps; no invented panel, no crash, no placeholder team or shop (D-241) |
| G-42 | Glyph singleness | list every glyph used and count the meanings each carries | one meaning per glyph; the flame currently carries three and must be resolved (D-251) |
| G-43 | Derived-number integrity | inspect any repeated figure across facility or teammate rows | it varies where the sourced table says it varies; a constant in a computed slot is invented (D-252) |
| G-44 | Cross-frame coherence | place two scenario mockups side by side | bar fill, tint, radius and chip palette are identical; only scenario-composed content differs (D-250) |
| G-45 | Background layer audit | inspect the dimmed or scrolled-past region of every overlay screen | no nav label, activity name or sample state contradicts the scenario in focus (D-254, D-255) |
| G-46 | Derived-value disclosure | find every letter or number the artifact computes rather than records | its rule is printed in the UI and labelled client-sourced or ours (D-256) |
| G-47 | Badge contrast | measure all nine grade badge letters against all nine fills, in both themes | ink-strong passes 9.00 and above; **white on the light fills measures 1.20-1.44 and is banned** (D-258) |
| G-48 | Single spine | place the four scenario renders side by side in one artifact | identical section order and positions; only the goal region differs (D-257) |
| G-49 | No inheritance prediction | grep the Legacy surface for projected outcomes | zero. Published chances and observed results only; no "likely", "expected", or rolled value (D-264) |
| G-50 | Spark provenance | inspect any Spark row | its Legacy group and generation are visible, since generation halves the rate (D-261) |
| G-51 | Guest affordance | inspect a Guest Legacy | mutating controls are absent, not disabled, and no in-game currency figure is shown (D-263) |
| G-52 | Colour containment | grep for the three Spark fills outside the Sparks list | zero occurrences (D-265) |
| G-53 | No computed score | inspect Career Rank and Rating on a completion surface | they are entered values labelled as such, never derived, never defaulted to zero (D-270) |
| G-54 | Win title provenance | for each Major Win shown | it resolves to a calendar race row or to the curated composite title list (D-271) |
| G-55 | Fan scale separation | count the fan scales rendered in one widget | one. Entry gates, event gates, skill gates and the class ladder never share a readout (D-272) |
| G-56 | No uninterpreted glyph | grep grade rendering for `U/G` | absent, and no threshold is assigned to it (D-274) |
| G-57 | Mood arrow present | render all five mood tiers, plus the same pill in the log and the guided preview | each carries its directional arrow (up, up, neutral, down, down) beside the word. A colour-plus-word pill with no arrow fails (D-259) |
| G-58 | Deck slot integrity | build a deck with a stat card in slot six and a non-linked character in slot three | slot six is captioned `Friends` with its own type chip unchanged, no `Scenario Link` badge appears on the non-linked card, and every slot's diamonds and level agree with `base + 5 × breaks` (D-277, D-281, D-282) |
| G-59 | Prose-sourced figure | take every numeric in a design artifact that arrived from a write-up rather than a capture or dataset, and name its source | each traces to a client frame, a dataset field, or a measured value with a date. An untraced figure is either `unsourced` in this file or absent from the artifact — it never reaches seed data or UI copy (D-283, D-284) |
| G-60 | Correction completeness | after retiring a sourced literal (a cap row, a percentage, a tier string), grep the **retired** literal across all tracked files | zero hits, or every remaining hit is a line that quotes it as withdrawn/superseded and says so in place. A file that reads correctly in isolation is not evidence the correction propagated (D-285, D-286) |
| G-18 | Both themes | toggle to dark, repeat G-5, G-6, G-7 and G-13 | every pair passes in dark too; no component is light-only (D-103, D-105) |
| G-19 | Theme fork check | grep components for `dark:` utilities and theme conditionals | zero outside the theme override block (D-101) |
| G-20 | No flash of wrong theme | reload with the OS set to dark | first paint is already dark (D-104) |
| G-21 | Font identity | computed `font-family` of body, a heading and a numeral | resolves to the declared rounded face, never to a banned grotesque (D-110) — **RULING B5 (2026-09-27): C-8 exemption granted; system stack (`ui-sans-serif, system-ui, sans-serif`) is the shipped identity. G-21 technically fails on the system grotesque but is exempted per KI-6. The rounded face (Nunito/M PLUS Rounded 1s) is parked pending a C-8 dependency approval that has not been granted. The exemption is recorded here so the gate's failure is a known decision, not noise.**
| G-22 | Weight availability | request 500, 600, 700, 800 from the font's own CSS | all four served, no synthetic bolding (D-110) |
| G-23 | Icon weight | render every glyph at 20px solid black | legible as a filled shape, not as an outline (D-111) |
| G-24 | Sheen geometry | inspect the primary button's gradient stops | a single hard split, not a feathered range or a vertical ramp (D-112) |
| G-25 | Tint discipline | sample the six band tints **against the surface they are actually painted on**, which is `--raised` in the stat band | no saturated stat hue anywhere; dark set measures 1.09-1.11 and passes, light set measures 1.10-1.21 and **fails the documented 1.04-1.14**, pending the §11.9 ruling on which surface is normative (D-113, D-114) |
| G-26 | Micro-strip equivalence | read a run card with CSS hidden | the grade text is still announced (D-115) |


G-13 deserves its note: a sweep that only checks element presence and ARIA attributes will pass a button that renders "Add turn: NaN". Assert on rendered text.

---

### 12. Floor (design-side, never)

- Never weaken a root `CONSTRAINTS.md` threshold to make a design fit. Escalate to the human owner (root C-4 path, `AGENTS.md` escalation 4).
- Never add a schema column to make a mockup work. That is an Architect escalation with a PRD citation, or it does not happen.
- Never add a dependency (font, icon pack, chart library, animation library) without approval. Root C-8.
- Never ship a saturated chrome surface under text. D-3.
- Never use equine vocabulary in any artifact, including a prototype that will be thrown away. NFR-6 has no scratch exemption. <!-- lore-ignore-line class=1 cite=NFR-6 -->
- Never promote an `❌ UNVERIFIED` reference item into UI copy. D-20.
- Never seed a game numeric without a server qualifier and a source date, and never carry a JP value under a Global scenario name. D-227, D-228.
- Never delete or stub a state view to pass a gate. Root floor: no stub bodies.


---

## FRONTEND-BRIEF-AUDIT.md

Audit — "Frontend Development Document" brief vs the tree as it actually stands


Audited 2026-09-27. Read-only: this file is the only artifact produced, and it is untracked.

**Subject of the audit:** the 945-line brief `Frontend Development Document — Umamusume Trainer
Companion`, Phase 1 scope, supplied as an attachment (temp path
`…/attachments/677cf4da-8e76-43dc-bebb-d9fa6cab91ce/90fce50e-dc5a-4216-a9cf-b51b785ef2a8.txt`).

**Method note.** Every line marked VERIFIED below was checked directly against a file at a cited
line. Lines marked REPORTED come from a delegated cross-read of §6/§7 component literals that I
spot-checked but did not re-measure one by one; treat those as indicative, not certified.

---

### 0. The situation this audit starts from

The brief is not a greenfield instruction. A second concurrent session is implementing the same
surface **right now, uncommitted**:

| Path | State |
|---|---|
| `resources/css/app.css` | uncommitted, 11 → **243 lines**, `@theme static` at :21 |
| `resources/views/components/{guided-step,race-calendar,resource-strip,stat-band}.blade.php` | uncommitted |
| `DESIGN.md`, `PRODUCT.md`, `docs/design-research/{CONSTRAINTS,DESIGN,SCENARIO-DIFFERENCES,SCREENSHOT-MANIFEST}.md` | uncommitted |
| `KNOWN-ISSUES.md` (180 lines), `docs/design-research/FRONTEND-SPEC-DIVERGENCE.md` (229), `MECHANICS-TRANSLATION-TRIAGE.md` (234) | untracked, authored by that session |

Two consequences. First, any implementation of this brief edits files another agent is holding
dirty, which is a clobber, not a merge. Second, **a divergence audit of a frontend spec already
exists** (`FRONTEND-SPEC-DIVERGENCE.md` §1.1–1.7) and reaches the same conclusions I do on theme
direction and the font gate — so this file should be read beside it, not instead of it. That audit
argues about *Nunito*; this brief mandates *M PLUS Rounded 1s* and never names Nunito, so the two
documents are not pointing at the same artifact.

---

### 1. Blocking findings

#### B1 — The brief's font mandate contradicts the document that outranks it. VERIFIED
The brief states its own precedence in line 5: where it and `DESIGN.md` disagree, `DESIGN.md` wins.
Root `DESIGN.md` then rules, at §2.2:

- `DESIGN.md:106` — "no **external fonts** (offline constraint + C-8 dependency gate)"
- `DESIGN.md:108` — "UI text: `--font-sans: ui-sans-serif, system-ui, sans-serif`"

The brief's §4.2 mandates self-hosting M PLUS Rounded 1s at weights 400–900, and §20.3 forbids
"system-ui as primary". Both directly oppose the higher authority. The tree already implements the
*ruling*, not the brief: `resources/css/app.css:29` is `--font-sans: ui-sans-serif, system-ui,
sans-serif`. The brief's own §19.1 concedes the font is an unapproved open question, and §17 says a
dependency not listed in §2 must stop and escalate — so the brief contradicts itself here as well.
`KNOWN-ISSUES.md` KI-6 warns not to "fix" this by fetching a font.

**No `resources/fonts/` or `public/fonts/` directory exists. This is not a build task; it is an
owner C-8 decision.**

#### B2 — The brief's `DESIGN.md` citations resolve to a different file than precedence implies. VERIFIED
The brief cites `DESIGN.md` §3.5 (tokens), §4.1/§4.2 (type), §6.x, §8.x, §11 (open questions).
Root `DESIGN.md` is 317 lines and its only headings are 1, 2, 2.1, 2.2, 2.3, 3, 4, 4.1–4.6. There
is **no §3.5, no §11, and §4.1 means "Catalog index", not a type scale.** Those sections exist in
`docs/design-research/DESIGN.md` (1540 lines). So "DESIGN.md wins" silently imports the research
package as the authority while root `DESIGN.md` — the file at the cited path — contradicts it on
fonts, radius, shadows and theme direction. Every citation in the brief should be re-qualified to
one file or the other before a single component is built.

#### B3 — Energy and Fans are authorised on paper and absent from the schema. VERIFIED
`grep -rniE "energy|fans|turn_events|scenario_slots" database/migrations/` returns **nothing**, and
neither appears in `app/Models/`. Meanwhile:

- `docs/adr/0003-…:3` — "Status: **accepted by owner 2026-09-27.** Absorbs the pending items in
  ADR-0001 and ADR-0002", defining Energy/Fans/`turn_events` column names.
- `docs/adr/0001-…:3` — "accepted in part… §5 (schema) is superseded by ADR-0003".

So the brief's §6.7 energy gauge, §7.1 fan readout and §7.2 low-energy advisory are **buildable in
principle but blocked on a migration that does not exist yet**. The brief's §17 says "a screen needs
a field the schema does not have → do not add a column" — here the column is already ruled, so the
correct move is to write the migration, which is a separate, sequenced change, not a silent one.

Note the reverse error too: ADR-0001:16 lifts §6.11 **for Energy guidance only**, while brief §1.2
restates the §6.11 non-goal flatly with no carve-out, then §7.2 asks for the energy advisory. Right
outcome, wrong stated rule.

#### B4 — The layout entry point is currently broken, independent of this brief. VERIFIED
`resources/views/components/layout.blade.php:7` requests `resources/js/app.js`; the only files in
`resources/js/` are `app.ts` (0 bytes) and `bootstrap.ts`. That is a `ViteManifestNotFoundException`
on every real page — logged by the other session as `KNOWN-ISSUES.md` KI-1. The brief's §16 asks for
`resources/js/app.js`, so it would *coincidentally* fix KI-1, but nothing in the brief acknowledges
it. Any JS work here also collides head-on with the `app.ts`/TypeScript stack that `tsconfig.json`
and `vite.config.js` already commit to.

#### B5 — Alpine.js is named but not installed. VERIFIED
`package.json` devDependencies are `@tailwindcss/vite`, `axios`, `concurrently`,
`laravel-vite-plugin`, `tailwindcss`, `vite`. Brief §2 lists "Vanilla JS / Alpine.js" and §16 puts
"Alpine components" in `app.js`. §2 also permits vanilla JS, so every listed interaction is
achievable without a new dependency — **recommend dropping Alpine from §2 rather than seeking C-8
approval for it.**

#### B6 — The brief omits the two screens the repo calls highest priority. VERIFIED
Root `DESIGN.md:179` heads §4.1 "Catalog index `/umamusume` **(first surface, owner priority)**",
and §4.5 specifies the review queue at `/review`. The brief specifies four screens — dashboard,
guided input, run list, skill search — and **has no file for catalog, catalog detail, review queue
or the CSV/JSON export affordance**, none of which appear in §7 or §16. `routes/web.php` carries 17
routes today. Adopting the brief as written therefore deletes owner-priority surface unless
"three screens plus a skill search" in §1.1 is meant as *additional*, not *exhaustive*. That reading
needs the owner, not an implementer.

---

### 2. Citation integrity (spot-checked)

| Brief claim | Verdict | Evidence |
|---|---|---|
| §1.2 "§6.12 — no race-day snapshots" | **MIS-CITED** | `PRD.md:92` = §6.11 "No race simulation, prediction engine, **or race-day snapshots**"; `PRD.md:93` §6.12 is "no dual storage modes". §6.12 is double-cited by rows 5 and 7. |
| §1.2 rows for §6.1, §6.9, §6.6, §6.13 | CONSISTENT | `PRD.md:81,89,86,94` |
| §2 "Governing: ARCHITECTURE.md §2" | **MIS-CITED** | §2 is System Design; stack is §1, layout is §9 (REPORTED, not re-read) |
| §13 "Governing: DESIGN.md §1.7" | **BROKEN POINTER** | no §1.7 exists in either DESIGN.md; in `docs/UMAMUSUME_REFERENCE.md` the terminology map is **Section 6**, §1.7 survives only as a stale cross-reference |
| §19.7 product name "closed: Trainer Desk" | CONSISTENT ruling, **contradicted by the brief's own title** | root `DESIGN.md:1` "Trainer Desk design system"; brief H1 says "Umamusume Trainer Companion" |
| §19.5 mood strings "resolved from client capture" | CONSISTENT | reference §1.1.6 confirms all five tiers from the client panel; `a7cabc0` is that commit. **But** the tier *colours* are provisional, and §6.14 then renders tiers with raw Tailwind `amber-*` instead of the `--color-mood-*` tokens §3.1 mandates. |
| §19.6 dark mode listed as open | **MIS-CITED as open** | research `D-104` already decides it; brief §3.2 implements exactly that (REPORTED) |
| §19.9 tint surface "decided" | **MIS-CITED as decided** | research §11.9 keeps it open (REPORTED) |
| §6.11 "fixed" discipline glyphs, Power = dumbbell | **CONFLICTS** | research §6.0b measures a *flexed arm* and marks it unresolved; Guts-as-flame triple-loads under D-251 (REPORTED) |
| §13 mandates `Pal` as a client label | **UNSUPPORTED** | reference sources "Pal" to game8.co, not a captured client string; `D-278` forbids shipping it as client copy and `D-20` bars promoting a `❌ UNVERIFIED` item into UI copy |
| §11/§15/§17/§18 vs root CONSTRAINTS C-1..C-8, Floor | CONSISTENT | no threshold in the brief requires relaxing the root bar — except the font, which *uses* C-8's approval route unilaterally |

---

### 3. Section status — as built today

Compact; component-literal rows are REPORTED.

| Brief § | Status |
|---|---|
| 3.1 `@theme` tokens | **PARTIAL + CONFLICTS** — most hexes present and exact; `ink-muted`, `--color-up`, `--color-down` differ; 5 mood tokens, radius/shadow/font tokens **missing**; ~15 unlisted tokens added, which brief:64 forbids; block is `@theme static`, not `@theme` |
| 3.2 dark theme | **PARTIAL** — dark override present at `app.css:152`; the head script exists *only* in `design-preview.blade.php`, not in the real shell, and ignores `prefers-color-scheme`. Root `DESIGN.md` reverses dark-first → **light-first**, uncommitted |
| 3.3 colour roles (gains orange, losses blue) | **MET** — honoured in `guided-step:90`, `stat-band:116` |
| 4.1 type scale | **NOT BUILT** — no `numeral-xl/lg`, `title`, `micro` roles; literals use `text-2xl`/`text-xs` |
| 4.2 font loading | **BLOCKED (B1)** |
| 5.1 two-region frame | **NOT BUILT** — `layout.blade.php:18` is one `max-w-5xl` column |
| 5.2/5.3/5.4 spacing, radius, elevation | **CONFLICTS deliberately** — `app.css:18-19` states "No shadows and no custom radius tokens… `rounded-md` throughout", matching root `DESIGN.md` §2.3 against the brief |
| 6 components (15 specified) | **1 PARTIAL, 4 inline, 10 NOT BUILT** — only 5 components exist and just `stat-band` name-matches; `race-calendar` is not in §16 at all |
| 7.1 dashboard | **NOT BUILT** — though scenario-composed strip and the two-marker cap rule are met |
| 7.2 guided input | **MET for the rail on the run screen (2026-09-28, `d50a0ec`/`6a53c15`)** — "Step N of M" ✓, bars not dots ✓, and keyboard 1–5/Enter/Escape now present: `resources/js/guided-flow.ts` binds the digits and Escape, arrow-key roving is the native radio group's, and Enter advances to the preview stage. Verified with pressed keys, not `element.focus()`. The audit's stated cause, "`app.ts` is 0 bytes", is fixed: `app.ts` is two imports. |
| 7.3 run list / 7.4 skill search | **NOT BUILT** — no skills route exists at all |
| 8 motion | **NOT BUILT** — zero transitions/keyframes/`prefers-reduced-motion` |
| 9 accessibility | **PARTIAL, narrowed 2026-09-28** — `tabular-nums` ✓; the focus-visible ring is now on the rail's choice banners (`2px solid --color-ring`, verified with a real Tab, `6a53c15`). The `role=radiogroup` ✓ this row counted was itself the defect: its children were `role="radio"` buttons claiming semantics they did not implement, now real radio inputs (KI-14). Still absent: `aria-live`. |
| 10 four states | **PARTIAL** — empty is a bare `<p>`; loading and error states absent |
| 11 iconography | **MET for the 5 discipline glyphs** (filled paths, no stroke, `stat-band:32-36`); sparkle for `is_unique` absent |
| 13 terminology | **PARTIAL** — `turn_entries.condition` still renders as "Condition", which §13 bans for Mood; and `mood` vs `condition` is ADR-0001's own open schema item |
| 15 testing | **NOT BUILT** — all 14 test files are backend/fetch/parse; §15.1 visual regression has no harness in `package.json` |

---

### 4. Interaction with the label-layer commit

`feat/enum-labels` @ `b5864ed` (3 behind `master`) adds `app/Enums/HasLabel.php`, `lang/en/uma.php`
and converts 10 Blade display sites. It is **orthogonal to and compatible with** the brief: it adds
no schema, and its `lang/en/uma.php` is the file §13's terminology belongs in — §13's bans
(Carats/Spark/Scouts/Mood/Trainee) already clear the `lore-code` patterns. Two frictions:

1. It edits six views that a §5.1/§7 redesign would rewrite → rebase `b5864ed` **before** the
   redesign, not after, or its label calls get lost.
2. `lore-code` does not currently grep `lang/**`; that commit widens it. Anyone reordering merges
   should keep that widening, since it already caught banned vocabulary inside the brief's own
   recommended phrasing.

---

### 5. Not verified by this audit

- Per-pixel component literals in §6 (grade badge 22px vs the built 20px at `stat-band:98`, the 50
  energy-risk tick, 26px capsule strip) are REPORTED from a delegated read; I confirmed the 20px
  finding's file and line but did not re-measure the rest.
- Whether `FRONTEND-SPEC-DIVERGENCE.md` audits *this* brief or an earlier draft of it. Its §1.6
  argues about Nunito, which this brief never mentions — likely a different revision.
- `docs/design-research/DESIGN.md` and `CONSTRAINTS.md` are dirty; every citation into them
  describes the working tree, not any commit.

---

### 6. Decisions needed before a line of this is built

1. **Font (B1).** Confirm root `DESIGN.md` §2.2 stands (system stack, no font dependency) and order
   §4.2/§20.3 deleted from the brief — or grant C-8 approval and say so in writing.
2. **Which DESIGN.md is authoritative (B2).** Re-point every citation in the brief, or state that
   `docs/design-research/DESIGN.md` outranks root `DESIGN.md` on tokens and type.
3. **Schema sequence (B3).** Land the ADR-0003 migration (Energy, Fans, `turn_events`,
   `scenario_slots`) as its own change before building §6.7/§7.1/§7.2 on top of it.
4. **Screen scope (B6).** Confirm whether catalog, catalog detail, review queue and export stay in
   Phase 1. If yes, the brief needs four more screen specs; if no, PRD US-1/US-5/US-6 are being cut
   and that is a scope change for the Architect.
5. **JS stack (B4/B5).** Drop Alpine and settle `app.ts` vs `app.js`, fixing KI-1 either way.
6. **Merge order.** Rebase `b5864ed` onto `master` before any view rewrite begins, and get the other
   session's dirty files committed so implementation starts from a tree nobody is holding.


---

## FRONTEND-SPEC-DIVERGENCE.md

Frontend spec — divergence audit


A pasted synthesis titled *"Umamusume Trainer Companion — Frontend & User Flow"* describes this app
as the product of the design corpus. Audited 2026-09-27 against the shipped CSS, `DESIGN.md`,
`CONSTRAINTS.md`, `PRD.md`, and the scenario data.

**Disposition: unmerged, per D-283.** It is a better summary of the corpus than most of the corpus,
and it is wrong in eight specific places, three of which would undo rulings that measurement or the
owner already settled. It also exposes two real gaps in our own docs, recorded at the end.

The document describes **our tool**, not the client, so unlike the two incoming write-ups this one is
checkable line by line against code. Every verdict below cites a file and line.

---

### 1. Where it disagrees with the repo

#### 1.1 Theme default is inverted — owner ruling, not a preference (blocking)

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

#### 1.2 The token table gives one hex per role, and one hex is a rejected value

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

#### 1.3 `~70+ turns` — the banned denominator, third appearance

Flow 2 is headed **"Log a Turn (repeated ~70+ times)"**. D-136 forbids exactly this and G-31 greps
for it: no source in this repository records a total turn count. The same family has now produced
"~50 rounds" (withdrawn), "70+ turns" (rejected in `MECHANICS-TRANSLATION-TRIAGE.md:83`),
`Turn 24/72` (rejected in §7.2 of that file), and this. It survives in prose that is otherwise
careful, which suggests the figure feels like scene-setting rather than a claim — it is still the
value an implementer would reach for when a progress bar needs a denominator.

#### 1.4 Legacy step uses the copy the ruling bans

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

#### 1.5 "Screen E — Race Calendar" collides with existing artifacts

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

#### 1.6 Nunito is presented as settled; it is parked behind C-8

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

#### 1.7 `Energy +5` on Wit training is the rejected figure re-imported

Screen B's preview row reads **"Wit Training (+9, Speed +2, SP +4, Energy +5)"**, and the advisory
says **"Wit costs 0"**. `MECHANICS-TRANSLATION-TRIAGE.md:52` already rejected the `+5`:
`UMAMUSUME_REFERENCE.md:97` records Wit recovery as *"a small amount"* with no figure, and the +5 in
our data belongs to a **Unity Cup Spirit Burst** bonus, a different mechanic. "Costs 0" is a further
step beyond the source: recovering some energy is not costing none. Both read as invented constants in
the one component whose purpose (P2, D-256) is to show only sourced numbers before a commitment.

#### 1.8 Two caveats dropped

- **"Danger" band below 30** appears without §6.15's own annotation that the 30 boundary is **an owner
  ruling, not a game fact** — no source publishes a threshold below 50 (`ADR-0001` §3). Dropping the
  caveat turns a house rule into a claim about the game, which is what D-256 and G-46 exist to prevent.
- **Support Card Rail "(if any)"** on the dashboard is quoted faithfully from §6.5b, but §6.5b itself
  sits against `PRD.md` §6.9, which excludes a support-card database from Phase 1, and
  `docs/adr/0005-support-card-entities.md:3`, whose status is **"PROPOSED. Not accepted, not built."**
  The rail cannot be populated from data the tool is not allowed to hold. This is a tension **inside
  our own corpus** that the document inherited rather than introduced — recorded below as gap 2.

---

### 2. Where it is right, and useful

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

### 3. Two gaps in our own corpus that this document exposed

1. **No screen-letter register.** §8 names A–D; E and F exist only in mockup filenames and have been
   reused at least four times (Run Completion, Run List, Scenario Switch, and Skill Search under F
   while D is its §8 letter). Any new artifact will keep guessing. Fix: an explicit table mapping
   letter → surface → owning section → artifact, with panels (Race Calendar) excluded from letters.
2. **§6.5b requires a Support Card Rail while §6.9 bars the data for it.** The rail is specified as
   persistent dashboard content, and PRD §6.9 excludes a support-card database from Phase 1, with
   ADR-0005 still PROPOSED. One of the two needs to yield, and it is a scope question for the owner.

---

### 4. Actions

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

### 5. Rulings received 2026-09-27 and what they changed

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

### 6. Second batch: scenario flows and UX behaviour (2026-09-27)

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

#### 6.1 Errors worth recording

| Where | Problem | Authority |
|---|---|---|
| `ux:178` | Cap-stack example prints **`+0 breakthrough`** | Prohibited by name at `docs/design-research/DESIGN.md:994` ("A term the tool cannot observe never renders as zero"). Same rejected value as the live string at `stat-band.blade.php:153` |
| `ux:98, 447, 100` | Theme, display timezone and the failure-estimate toggle persist to **`localStorage`** | Owner ruling 2026-09-27: preferences go to **SQLite**, because `PRD.md` §6.12 cuts browser-side storage as a second source of truth. Now written into D-104 |
| `ux:29` vs `ux:98` | The doc says "Default to light theme" and then defaults Theme to **System** | Not actually a contradiction — the corpus resolves it: light is the *base palette*, resolution is stored → `prefers-color-scheme` → light. The doc states the rule and the fallback without connecting them |
| `ux:17` | "Run List … **Default landing**" | `DESIGN.md:181`: the catalog index is "**The desk's front page** and the most complex grid", owner priority |
| `ux:247-262` | One universal 4-step turn flow | `config/scenarios.php:83,116` compose per-scenario `steps` (Unity Cup `facility`, Trackblazer `shop`); a fixed 4-step flow is the scenario-hardcoding D-240/G-40 forbids |
| `flows:327-342`, `839-841` | Adds League Rank, Team Race countdown, GP deadline and restock timers to the **resource strip** | `DESIGN.md:1020` caps the strip at zero-to-five items (turn, Energy, Fans always, then the scenario's widgets in `config/scenarios.php:82,115`). These facts are sourced; they belong in panels, not on the strip |
| `flows:968-983` | Race Fatigue chip prints bare numbers ("Mood Down 33%") | Values trace to `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` via `SCENARIO-DIFFERENCES.md:179-185`, but **D-230 as amended requires client data or two sources before a percentage prints as a number**, and `90%+` is a lower bound. Written before that rule existed |
| `flows:188-190` | "need **3+ of 5** to win" the Team Race | Misreading. `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` section "Unity Cup Team Races" says "aim for at least **3 circles**" — a displayed win-odds *estimate* used as a safety margin, not a victory condition |
| `flows:576` | Item called "**Anklet**, 50c" | `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` section "Full Shop Item List" names it **Ankle Weights**, and records that **no Wit version exists** — a constraint the spec drops |
| `flows:27, 468` | "inheritance" used as the system noun | `REFERENCE:571` plus the owner ruling: system = **Inspiration**, screen = **Legacy Select** |
| `ux:37` | Cites "DESIGN §6.20" for cards-not-a-table | §6.20 is the Race Calendar. The rule is D-60 / `DESIGN.md:1301`. Right rule, wrong pointer |
| `flows:377`, `359`, `1008` | Spirit gauge "0-100", "purple ring", "orange at full", Climax "1st of **5**" | No authority carries a gauge scale, those two colours, or a finale field size |

#### 6.2 Worth adopting (patterns, per D-283)

`UX §3` **input taxonomy** (every input, option, toggle, and an explicit "what is NOT an input") and
`§4` **run-lifecycle state machine** are structures the corpus does not have, and they are the right
neighbours to D-223's six-state burst machine. `flows §4.1/4.2` **component-state tables** are the same
idea at component granularity. Adopting the *shape* costs nothing and makes G-7 (four states per data
view) auditable; adopting their *contents* still requires the citations above.

#### 6.3 A cadence fact the corpus was missing

`flows` and `ux` both state Unity Cup Team Races occur **every 6 months**. That is sourced —
`docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` section "July 1, 2026 Update" and section "Gathering Team Members" — and the research corpus never recorded it, which is why an
earlier round could call it unsourced. It belongs in §10n's Unity Cup column and, if the strip or a panel
ever counts down to one, in `config/scenarios.php`. Not added here: that is a data change to a shipped
config file, and the phase is fenced against it.

#### 6.4 Consequences of the two rulings taken this round

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


---

## MECHANICS-TRANSLATION-TRIAGE.md

Mechanics translation — triage


An incoming design-language write-up reframed every game system as a UX pattern catalog. It is
a good pattern catalog and it must not be pasted into the design corpus. Reviewed 2026-09-27
against the gates, the measured evidence and the sourced tables already in this repository.

**Disposition: keep the patterns, reject the numbers, quarantine the vocabulary.** Roughly a
third of the document is either terminology this project bans, a figure already ruled
unsourced, a mechanic that does not exist on Global, or a claim contradicted by client frames
we have measured.

---

### 1. Terminology that would fail the gate outright

`gate.py`'s `TERM_BANNED` and the `lore-code` target enforce these on any shipped surface. The
document uses each as its **primary** vocabulary, not incidentally, so ingesting it would put
banned words in the source of truth that implementers read.

| Document term | Where | Gate rule | Required term |
|---|---|---|---|
| "Motivation" | §3.2 heading "Mood (Motivation)" | `\bmotivation\b` → use Mood | **Mood** |
| "Factor", "Factors", "Factor Farming", "factor triggers" | §6 throughout, §1.2, §15 | `\bfactor\b` → use Spark | **Spark** |
| "Inheritance" as the system name | §6 title, §6.1, Flow 6 | not gated; `UMAMUSUME_REFERENCE.md:571` records the `[Global]` system name | **Inspiration** (see the naming note below) |
| "Parents" / "grandparents" as the system's nouns | §6.1, §6.2 | `[Global]` calls the chosen ancestors legacies | **Legacies** |
| "Pal/Friend" | §5.1 | `\bfriend\b` → use Pal | **Pal** — but `Pal` is Game8's word, never read off a client frame (D-278). Use it as a working label, not as a captured string |
| "Goddess's Wisdom" | §9.5 | `\bwisdom\b` → use Wit | see note |
| "parents", "grandparents" | §6.1, §6.2 | owner ruling, `CONSTRAINTS.md` D-267 | **Legacy**, **Ancestor** |

Three notes. **"Inspiration" versus our own heading:** `UMAMUSUME_REFERENCE.md:571` records `[Global]` localising the *system* as Inspiration, with *legacies* for the ancestors and *Sparks* for the traits, and the owner confirmed that set on 2026-09-27 (root `DESIGN.md` §6, `PRODUCT.md`), naming the screen **Legacy Select** — so the vocabulary is settled, not open. The residual is evidentiary only: neither caption has been read off a frame, so both are this tool's words and neither may be styled as the client's. "Goddess's Wisdom" is a proper noun for a resource, not the Wit stat, so it is a
naming landmine rather than a violation — except that the scenario it belongs to is not on
Global at all (see §3), so the question does not arise. And "Factor" is the single most
consequential one: it is the JP term for what the Global client calls **Sparks**, and the
document uses it as the section title, the loop name and the variable name. Every one of those
would fail `make lore-code`.

The document does get the monetisation vocabulary right independently: **Scouts**, **Carats**
and the exchange-point model all match the terminology table.

---

### 2. Claims contradicted by evidence already measured in this repo

| Document claim | Contradiction |
|---|---|
| §4.3 "**Speed > 2000** unlocks Full Spurt" | **Internally inconsistent within the incoming document — and this row's original verdict was too strong.** §4.2 of the same document states the hard ceiling *is* 2000, so the two sentences cannot both be about one scenario. But the triage then wrote "a gate above the maximum possible value cannot exist", and that is wrong engine-wide: `hard_caps` is **per scenario**, 2000 for the twelve older ones and **2500** on Beyond Dreams, whose scenario cap is `1200 + 900 = 2100`. `UMAMUSUME_REFERENCE.md` Source Conflict Log row 3 reconciled exactly this on 2026-09-27 rather than leaving it open. What is true, and what matters to this tool: on `[Global]` no scenario cap exceeds **1900**, so a >2000 gate is unreachable for the audience this product serves. The correct rejection is "unreachable on Global, and the document states its own ceiling as universal when it is per-scenario" — not an absolute impossibility. |
| §4.2 "Baseline cap 1200, soft ceiling, gains beyond reduced" | **Corroborated.** Matches D-211 exactly. |
| §4.2 "URA = 1400 all; Unity Cup = 1300 except Wit 1800; Trackblazer asymmetric" | **Corroborated** against `scenarios.json`. Trackblazer is specifically 1200 / **1900** / 1200 / 1200 / 1500. |
| §6.2 "Blue / Pink / Green sparks" | **Corroborated** and measured: `#3CB4F0`, `#FC84B4`, `#90CC30`. |
| §6.2 adds "White Sparks" and "Scenario Sparks" | **Confirmed, not merely corroborated — the original wording understated our own evidence.** `UMAMUSUME_REFERENCE.md:585-592` tabulates all six Spark categories with `factors.json` export record counts: Stat 5, Aptitude 10, Unique skill 268, Skill-White **452**, Competition-White **37**, Scenario **34**, each graded `[A]+[A]+[B]`. So existence and taxonomy are data-backed. What is *not* evidenced is the visual treatment: no captured frame shows a Scenario or White Spark bar, so the categories may be named and counted while their rendering stays unverified. Two different claims, and only the second one belongs to the design corpus. |
| §6.1 "two ancestors, each brings two of theirs, six-character lineage" | **Corroborated** by the Legacy Select frames: two large portraits, each with two smaller ones. |
| §9.3 "Extreme Spirit Burst guarantees success" | **Corroborated.** D-231: it sets that facility's failure rate to 0%. |
| §9.4 "restocks every 6 turns" | **Corroborated.** Recorded in `config/scenarios.php`. |
| §3.2 "five discrete states Peak → Worst, +20% to −20%" | **Partly contradicted.** Only `GREAT` and `GOOD` are evidenced as Global client strings (§11.5, D-20). "Peak" and "Worst" are inventions, and the −20% end of the range is unsourced. |

---

### 3. Not on Global, so out of scope for this tool

The product is a Global-only companion. Three sections describe systems that do not exist
there, and documenting them as UI requirements would create work for surfaces that cannot
appear.

- **§9.5 Grand Masters and the Three Goddesses.** `scenarios.json` gives scenario id=5
  `start_en: null`. It is JP-only. Its linked characters carry real-world honour names rather
  than Umamusume ones — the specific vocabulary is left unsaid here because NFR-6 bars it in any
  artifact, including this one — so the naming would need a Lore Guardian ruling even if the
  scenario shipped, and it introduces a resource literally named "Wisdom".
- **§14.2 Independent Training idle mode.** Marked `[JP]` by the document itself.
- **§14.3 Skill Set bulk planning.** Marked `[JP]`.

Also out of scope on a different axis: **§5 support cards**. `PRD.md` §6.9 excludes a support
card database from Phase 1, and §10.1's setup wizard step "Select Support Deck" therefore has
no schema behind it. The patterns are worth keeping for a later phase; they are not
requirements for this one.

---

### 4. Numbers the document asserts that this repo has already ruled unsourced

The pattern observations around these are good. The figures are not, and several are the exact
kind of plausible constant this project has been catching all session.

| Claim | Status |
|---|---|
| §1.2 "typically **70+ turns**" | **Rejected.** D-136 and gate G-31: no source in this repo records a total turn count, and an earlier "~50 rounds" claim was already withdrawn for the same reason. This is the same defect with a different number, and a larger one. |
| §3.1 "failure probability rises sharply below **~50**" | **Unverified.** A "below 30" variant was tested and rejected earlier; ADR-0001 concluded failure rates cannot be sourced and must ship as bands. |
| §3.3 "each Hint Level reduces SP cost by **~10%**, up to **30%** at Level 3" | **Unverified.** Direction is plausible, figures are not sourced here. |
| §6.2 stat sparks "**+5 / +12 / +21**" | **Unverified** against anything in this repo. |
| §7.1 leg fractions "**16.67% / 66.67% / 83.33%**" | **Unverified.** Internally consistent, which is not the same as sourced. |
| §5.2 bond gauge "**80**" threshold, "0–100" | **Unverified** here, though consistent with the "just under 1 full bar" reading of 19/80 seen in the Trackblazer bond requirements. Weak corroboration only. |
| §11.1 exchange points "**200**" | **Unverified** here. |

The reusable form of each of these: keep the **pattern** ("a progress-gated unlock with a
visible threshold"), and treat the **number** as unsourced until it is traced to the client or
a dataset. That is the same rule D-256 already applies to the grade ladder.

---

### 5. What is genuinely useful and should be kept

The translation itself is the valuable part, and most of it survives.

- **§1.1 "informed risk-taking"** names the principle this package implements as the preview
  step and D-171's energy-beside-confirm rule. It is a better phrase for it than anything
  currently in `DESIGN.md`.
- **§4.3 behaviour gates** is the strongest single design observation in the document: a stat
  threshold that unlocks a *named behaviour* is a different thing from "more is better", and it
  is exactly why the 1200 halved-gains line is drawn as its own marker in §6.5 rather than
  being folded into the bar end. The specific thresholds are unusable; the principle is right.
- **§5.3 friendship training as a spatial puzzle** — pattern recognition and timing over a
  simple picker — matches the occupancy flames requirement in §6.24.
- **§6.4 factor farming as the meta loop** ("output of one run becomes input of the next") is
  the cleanest statement in the corpus of why Legacy provenance matters, and it supports D-261.
- **§10.1 the six-step pre-run wizard** corroborates D-260's ordering and extends it.
- **§14.1 decision transparency** and **§15's principle table** are a fair summary of P1–P6.

---

### 6. Actions taken

1. This file records the triage. The source document is **not** merged into `DESIGN.md` or
   `CONSTRAINTS.md`.
2. `CONSTRAINTS.md` gains **§10t** with **D-283** (a design write-up is a source of patterns,
   never of values, copy or vocabulary) and **D-284** (a pattern adopted from prose needs an
   evidence anchor this package already holds), plus gate **G-59**. An earlier draft of this
   list named D-280 and G-57; both were already taken, D-280 by the Unique Perk rule and G-57
   by the mood-arrow check. Renumbering a shared doc breaks inbound `§`-anchors, so the
   collision is the reason the new numbers were chosen rather than a detail to smooth over.
3. Two patterns are adopted into `DESIGN.md`: **P2** is renamed to carry "informed risk-taking",
   because the phrase names the Trainer's act rather than the UI's, and **§6.5** gains the
   behaviour-gate rationale for keeping the 1200 halved-gains line apart from the scenario
   ceiling. Both were already implemented; the adoption gives them a name and an argument, not a
   new rule.
4. Nothing in §3 or §4 of this file becomes a UI requirement, a seed value, or a piece of copy.
   The one exception is a *negative* one: the numbers stay in this file as rejected claims, so a
   future round does not re-introduce them from the same source.

---

### 7. The expanded deliverables variant

A second document from the same source family arrived the same day, attached at
`docs/UMAMUSUME PRETTY DERBY — COMPREHENSIVE UX DELIVERABLES.md`: 2,133 lines in three parts —
31 specified components (`UM-<SYSTEM>-<N>`), 13 flow blocks, and a heuristic evaluation. Audited
against the authorities the same way §1–§5 audited the essay version.

#### 7.1 It is a mirror, and the mirror found a defect in our own data

The document claims at line 5 that its numbers are "drawn from the source-cited reference guide
(compiled 2026-09-27)" — that is, from `docs/UMAMUSUME_REFERENCE.md`. The claim checks out, and
that is what makes it interesting rather than dismissible:

- Its Trackblazer cap row (line 862) is `1200 / 1900 / 1200 / 1500 / 1200` — a verbatim copy of
  `UMAMUSUME_REFERENCE.md:357`, **including the Guts/Wit transposition** that
  `SCENARIO-DIFFERENCES.md:104` diagnosed. The diagnosis was correct and the fix had been applied
  to §1.6's Trackblazer row (`REFERENCE:758`) but never to the source table that produced it. The
  outsider was right about our data being wrong; the error was ours.
- Its mood table (line 233) still asserts `[Global]` client strings are "❌ UNVERIFIED", mirroring
  an *earlier revision* of `REFERENCE` §1.1.6 — a status closed on 2026-09-27 at `REFERENCE:167`
  and recorded as closed in `ADR-0001` §6.

So two artifacts agreeing is evidence of a shared source, never of that source being correct. This
is `CONSTRAINTS.md` **D-285**, and **D-286** generalises the part that cost us a round: a
correction is not finished until every copy of the retired literal is gone.

#### 7.2 Its numbers, classified

| Claim | Verdict |
|---|---|
| :862 Trackblazer `… / 1500 / 1200` | **Contradicted — and it was our stale row.** Authority: `REFERENCE:758`, `config/scenarios.php` `cap_bonus` (Guts 0, Wit 300) |
| :220-224, :1303 mood pre-race `+10 / +5 / 0 / −2 / −5` | **Contradicted.** The client's Mood Effect panel prints `+4 / +2 / 0 / −2 / −4` (`REFERENCE:157-163`). Note the shape of the error: the `−2` is the client's, the top two tiers are the stale 2021 Kamigame gloss (`REFERENCE:165`) — a chimera of two sources, which no single source supports |
| :867 "Hard ceiling **across all scenarios**: 2000 (from `scenarios.json`)" | **Wrong as stated.** 2000 holds for the Global-live set; two JP-only rows carry 2500 (`SCENARIO-DIFFERENCES.md:122`, `ADR-0002:97`). Immaterial to a Global-only tool, still a false sentence |
| :64 "shop items can **temporarily** raise a facility level" | **Contradicted.** Training Application is 150 coins and raises it "permanently for the run" (`docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md`) |
| :1619 "+8 Max Energy (**55** coins)" | **Contradicted.** `Energy Drink MAX EX` is **+8 Max Energy at 50 coins** (05:71). The rest of that shop list is accurate, which is what makes the one wrong price dangerous |
| :1176 `"Turn 24/72"` | **Rejected.** D-136 / G-31: no source records a total turn count. This is now the *third* invented denominator from this family — "~50 rounds", "70+ turns", and 72 |
| :178 critical at ≤25 **vs** :91, :1220 critical at `<17` | **Internally refuted**, and neither is sourced. Only the 50-Energy line is on record (`REFERENCE:170`), attributed there to GameWith 2026-09-25 — not to "Game8" as :1694 states |
| :177, :179 base max Energy 100 | **Unsourced.** Only the +12 raise and the +4/+8 items are recorded |
| :51, :143, :193, :102 Wit "+5" Energy recovery | **Unsourced.** `REFERENCE:97` says "a small amount"; the +5 figure is a Unity Cup *burst* bonus wearing the wrong hat |
| :53, :101, :1219, :1702 a failure **percentage** in the UI | **Barred by conclusion, not taste.** ADR-0001: no curve exists to render; bands only |
| :275-281 bond bands `0-19 / 20-39 / 40-59 / 60-79` | **Unsourced.** `REFERENCE:443` supports the 0-100 scale and the 80 mark, nothing between |
| :674 "340 SP", :687 "Reach 1200 Power" as an evolution condition | **Unsourced / misattributed.** The 170 belongs to Swinging Maestro; 1200 Power is Grand Live's *Fully Charged* gate |
| :1633-1663 the Grand Masters goddess flow (fragment ratios, +20-35% hint, 10-23% energy discount, +35% race stats) | **Wholly unsourced**, and the scenario is `[JP-Only]` — `scenarios.json` id=5 `start_en: null` |

#### 7.3 Vocabulary and scope

The document's own conventions forbid its headings: line 18 requires `[Global]` client strings as
primary labels, and its terminology map at :1098 correctly maps 因子 → **Spark**, yet §1.5 and Flow 6
lead with "**Factor**" (51 bare occurrences), and :155/:206 with "**Motivation**". It also uses
"parents"/"grandparents" for Legacy slots, which D-267 bars in copy. `make lore-code` would reject
it, and a mirror's correct table does not launder the violation three sections later.

On scope, most of Part 1 is not buildable here: §1.3 support cards and Flow 3 (PRD §6.9 — and note
`ADR-0005` is **PROPOSED, not accepted**, so the bar is current, not settled-aside), §1.5
inheritance as specified (PRD §6.3 "two optional parent references, nothing more"), §1.7 live
operations (PRD §6.6), §1.8 gacha (`ADR-0001:21` names it explicitly out of scope). Race playback,
skill cut-ins and result modals are the client's screens, not a companion tool's. Flow 4 step
"Race Simulation" and 10A's bare "Recommend" hit PRD §6.11 and Planner Rule 5 directly.

One omission is worse than any inclusion: **Our Grand Concert**, the fourth scenario this tool's
own `config/scenarios.php` carries, appears only as a cap row (line 863) and gets no flow, no
component, and no widget — while a JP-only scenario gets fifteen.

#### 7.4 What survives

- **UM-TRM-001's design rule** — "never mix server terminology in a single UI; resolve every label
  to the active server's client strings before rendering" — is a cleaner statement of what D-278
  and D-235 already require. Recorded as corroboration; no new rule, because the rules already hold.
- **Part 3's three systemic issues** independently confirm the premise this product rests on: the
  client surfaces mechanically rich systems as opaque outcomes (H10 gain formula undocumented,
  GH3 cognitive load, H9 failure not explaining cause). Our printed-rule disclosure (D-256) and
  §6.5's two markers are the answer to exactly that. Its own *fix* for H1 — display the failure
  percentage — is the one thing here we cannot take.
- **The component inventory** is a useful negative check: it lists client surfaces this package has
  never specified (course conditions, running strategy selector, objective tracker, camp banner).
  Whether any becomes a requirement is a PRD question, not an ingestion.

#### 7.5 Actions taken

1. `UMAMUSUME_REFERENCE.md:357` corrected to `1200 / 1900 / 1200 / 1200 / 1500`, with the
   pre-correction figure and its diagnosis path recorded in the row's own source cell so the next
   copier inherits the reason, not just the number.
2. `SCENARIO-DIFFERENCES.md:104` retensed — it asserted in the present tense that line 357 *records*
   the wrong row, which became false the moment 357 was fixed.
3. `CONSTRAINTS.md` §10t gains **D-285** (a document that cites us is a mirror, not corroboration)
   and **D-286** (a correction is not finished until every copy is gone), plus gate **G-60**, which
   greps the *retired* literal rather than the edited file.
4. `ADR-0001` §6 deliberately **left as-is**: it carries a dated "RESOLVED" block above the
   superseded text, which is correct ADR hygiene and the reason G-60 must be read with judgement —
   quoting a retired value to record its retirement is not the same defect as copying it.
5. A triage banner was added at the top of the deliverables document itself, in place rather than
   only in this file, because a 2,133-line artifact of rejected values sitting unmarked in `docs/`
   is the failure mode D-285 describes.
6. No number in §7.2 becomes a seed value, a config entry, or UI copy.


---

## EXTERNAL-DESIGN-REVIEW-TRIAGE-2026-09-28.md

External design review — triage, disposition, erratum


**verified-against `ee6786c`** (`docs/audit-remediation`, 2026-09-28). Every line reference and
count in this file was read from that tree or re-run against it; §5 lists the commands. The rule
that put this header here is the owner's own, recorded in §1.

The review arrived as pasted text with no instruction attached. It was triaged rather than
executed: each finding was checked against the tree, then accepted, refused, or re-scoped. Three
of its claims did not survive that check and the owner withdrew them.

---

### 1. Erratum — three withdrawn claims (owner ruling, 2026-09-28)

These claims were made in the review, were found false here, and are **withdrawn**. They are
recorded rather than deleted: a doc that silently loses a wrong claim teaches nobody that the
wrong claim was ever possible.

| # | Claim as written in the review | Ruling | What the tree shows |
|---|---|---|---|
| 1 | "Server-side/pre-paint theme lives only in design-preview; the real layout ships without it" | **Withdrawn** | `resources/views/components/layout.blade.php:10` renders `data-theme="{{ $theme }}"`, fed by the `View::composer('components.layout', …)` registered in `app/Providers/AppServiceProvider.php` |
| 2 | "The script hardcodes a dark fallback instead of reading prefers-color-scheme" | **Withdrawn** | the same file's inline head script reads `window.matchMedia('(prefers-color-scheme: dark)')`; nothing hardcodes a theme |
| 3 | "The mood column lives only on the unmerged `feat/adr-0003-schema` branch" | **Withdrawn** | `turn_entries` on this branch carries `energy`, `mood`, `fans`; the migration is tracked at HEAD (`database/migrations/2026_09_27_093945_add_energy_mood_fans_to_turn_entries.php:17`) |

**Root cause, in the owner's words:** "I reused `FRONTEND-BRIEF-AUDIT` findings without
re-verifying against HEAD. The audit was true when written and went stale as T-work landed."

**Two process rules took effect at that moment, and they bind this document:**

1. Every review or audit carries a `verified-against <SHA>` line at the top, so its evidentiary
   horizon is visible to any later reader.
2. Withdrawn claims are recorded as an erratum note on the document, never silently edited.

---

### 2. A caveat on the withdrawal of claim 1, found while reconciling the branch (T8/T9)

Claim 1 was withdrawn against the working tree, and the working tree does server-render the
theme. Re-verifying it against **HEAD** for this document found the two halves of that path sit
in different places:

```
$ git grep -c data-theme HEAD -- resources/views/components/layout.blade.php   -> 1
$ git grep -n "View::composer" HEAD -- app/Providers/AppServiceProvider.php    -> no match
```

The template that *consumes* `$theme` is committed. The composer that *supplies* it is not on any
ref — it is inside the concurrent session's uncommitted `AppServiceProvider.php` (+25/−1), and
`app/Models/Preference.php` with its migration exist on no ref at all (`git log --all --` on them
returns zero commits; KI-13). `layout.blade.php:15` gates the inline fallback with
`@empty($theme)`, so a clean checkout of `master` or of this branch renders **no** `data-theme`
attribute and falls through to `prefers-color-scheme` alone. US-11's stored-preference path is
unshipped on every ref.

This does not resurrect the review's recommendation and it did not change any file T4–T9 touched,
so the withdrawal stands as issued: the claim was tested against the tree in front of it and the
tree was right. It is recorded because the disposition's own §6 asks to be told about anything
that "revives one of the withdrawn claims", and a claim that is true of the branch while false of
the working tree is exactly that shape. The fix belongs to KI-13, not to the theme default, which
stays an owner call.

---

### 3. Disposition of every finding, as accepted

| Finding | Verdict | Disposition |
|---|---|---|
| Theme shell absent / no system-follow | False | Withdrawn (§1) |
| Mood column unmerged | False | Withdrawn (§1) |
| "No stat band" / "no guided flow" | Misframed | Re-scoped: both components exist, are tested and measure clean; the gap is that `design-preview.blade.php` is their only mount. → S4 |
| Raw turn form as only door; no preview, failure branch, Energy advisory | True | S4 (D-50/51/53/171/200) |
| `app.ts` 21 bytes; no keyboard path (G-11) | True | S4, last (D-55) |
| No two-region frame | True | S4 (D-40) |
| Strip not persistent | True | S4 (D-170) |
| "1 aliases" pluralization | True | Drive-by only if a file already touched by T4–T9 carries it → **not applied**, see §4 |
| Inventive skill names in the live select | True | Landed in T5 (`5c65597`) |

The reframe that matters: four "unbuilt features" were one wiring gap on the run view. That is
why it became one slice instead of four.

### 4. What T4–T9 actually did with it

- **T5 skill names (`5c65597`).** `SkillSeeder` now holds exactly the ten names from the approved
  D-210 pool, verbatim — `Gourmand`, `Unstoppable`, `Up-Tempo`, `Come What May, See Ya Later!`,
  `In Body and Mind`, `Homestretch Haste`, `Professor of Curvature`, `Traightaways`,
  `Playtime's Over`, `564 Escapades`. Nothing was composed, shortened or "corrected":
  `Traightaways` keeps D-210's spelling. `sp_cost` is seeded `null` and `type` left unset, because
  D-210 evidences names and nothing else, and attaching the old invented 120/60 to real skills
  would have been the worse failure (D-227). Both `Illustrative …` rows are deleted at the end of
  the seed — `updateOrCreate` alone would leave them in the live select. `Light Hello` was not
  seeded: it is a Pal support card, not a skill.
- **"1 aliases" — not applied, on the review's own condition.** The string is
  `resources/views/catalog/index.blade.php:43` (`{{ $umamusume->aliases_count }} aliases`). That
  file is not in T4–T9's touched set, and it is currently dirty in the concurrent session's
  working tree, so the instruction's fallback governs: left for the copy pass, no standalone
  commit.
- **Refused, with evidence.** Retirement of `--color-green-tint` (R14). The pair is live spec in
  `DESIGN.md` §6.15 for the unbuilt Safe-band colouring and the converged prototype uses it; the
  token is declared in both themes and survives `@theme static` (55 declared, 0 pruned against the
  built sheet). Recorded as the open half of KI-11 rather than deleted to quiet a count.

---

### 5. Commands behind §2

```
git grep -c data-theme HEAD -- resources/views/components/layout.blade.php
git grep -n "View::composer" HEAD -- app/Providers/AppServiceProvider.php
git log --all --oneline -- app/Models/Preference.php \
    database/migrations/2026_09_27_121500_create_preferences_table.php
git diff --stat -- app/Providers/AppServiceProvider.php
```

Line references for the withdrawn claims: `layout.blade.php:10` and `:15`,
`AppServiceProvider.php` (`View::composer('components.layout', …)`, the matchMedia script in the
same block), and the `mood` column at
`2026_09_27_093945_add_energy_mood_fans_to_turn_entries.php:17`.

### 6. S4 — opened, not started

Sequence as ruled, and it does not begin until T4–T9 is green and reported:

1. Mount `x-stat-band` and `x-guided-step` on the run view; add the `MoodTier` field to the turn
   form; wire preview-before-commit, the success/failure branch, and the Energy advisory beside the
   commit control (D-50/51/53/171/200, D-202/259, G-57).
2. Two-region desktop frame and non-scrolling resource strip (D-40, D-170).
3. Keyboard path for the guided flow (D-55, G-11) — last, because it only bites once the guided
   flow is the live path.

**Owner/Architect calls, unchanged and untouched by this slice:** theme default (light vs dark),
rounded-font vs system stack (C-8 plus `DESIGN.md` §11), mid-run "Change scenario" semantics.


---

## SCENARIO-DIFFERENCES.md

SCENARIO-DIFFERENCES


Comparative matrix of the three Global career scenarios, for scenario-aware UI design.
Sources: `docs/scenarios/01-ura-finale.md`, `02-unity-cup.md`, `03-trackblazer.md` (owner-supplied, retrieved with Claude), cross-checked against `docs/UMAMUSUME_REFERENCE.md` and `docs/design-research/RAW-FINDINGS.md`.
Compiled 2026-09-27, revised the same day after Global-side verification against Game8's English scenario guides, GameTora's Trackblazer and July-2026-rebalance pages, and `umamusu.wiki`.

**Warning for future readers of the owner-supplied files.** Two of the three were retrieved before the 2026-07-01 Global rework and are stale on status and numbers while remaining sound on mechanism. `02-unity-cup.md` files real, live mechanics under "Not Yet on Global"; `03-trackblazer.md` still describes a scenario that shipped six months ago. Read the resolutions below rather than trusting either file's figures.

### The headline

**The three scenarios do not share a resource model, a progression model, or a goal model.** A design that adds a scenario name to the header and scales a bar is not scenario-aware; it is scenario-decorated. The differences below change which panels exist at all.

| Axis | Ura Finale | Unity Cup | Trackblazer |
|---|---|---|---|
| Core loop | Train solo, scripted events | Team building | Player-directed racing |
| Scenario currency / extra system | **None** | Team Rank, Spirit Bursts, Result Pts | Grade Points, Shop Coins |
| Goal structure | Fixed mandatory race goals | Fixed race goals **plus** 5 Team Race rounds | **No fixed race list.** Grade Point deadlines, earned by racing |
| Facility level from | Repeating one stat 4x | **Team's** aggregate stat rank for that stat | Repeat one stat 4x **+ shop levels: 150 coins, permanent for the run** |
| Fan role | Gates 3 Unique Skill upgrade events | Secondary; racing is actively discouraged | Primary farming vector |
| Shop | No | No | Yes, in-run, coins from placement |

**Read the Trackblazer row carefully: no fixed race list is not the same as racing being optional.** Three facts the earlier short phrasing blurred, all from `05`/`04`: the objective list is **four items, starting with the Debut race**, then 60 / +300 / +300 Grade Points (lower for dirt-leaning trainees); Grade Points are earned **only by racing** (G1 1st = 100, G2 80, G3 60, scaled by placement); and missing a deadline **fails the run**. So racing is functionally mandatory and the *race log* is load-bearing data — the difference from URA and Unity Cup is that the Trainer chooses which races to enter, so there is no calendar to render. This matters beyond wording: `ADR-0003`'s `race_entries` was rejected here as "URA/Unity-Cup-shaped" on the strength of "no race goals", which was the wrong reason for a right conclusion — see the corrected argument at the end of this file.
| Scenario Link character | Aoi Kiryuin | 5 linked characters, chosen via Team Name | **None** |
| Finale | URA elimination races | 4 Team Races then Team Zenith, then URA-style finals | **Twinkle Star Climax: 3-race points league** |
| Secret character events | Yes | Yes | **Do not trigger** |

**This matrix covers three scenarios; the client offers four.** *Brighter Together: Our Grand Concert* has been live on Global since **2026-07-22** with a Speed cap of 1600 and five scenario-linked characters (`scenarios.json` order **4** — this line previously said order 3, which is Trackblazer), and no file in `docs/scenarios/` described it until `07-grand-concert.md` recorded it as a known gap. Everything below is therefore a three-of-four analysis, and the scenario-composition rules in D-220 must be written so a fourth scenario can be added without redesigning the strip.

### Per-scenario UI consequences

#### Ura Finale — the baseline, defined by absence

The guide is explicit: *"no team system, no shop, no mid-scenario currency."* So the Ura Finale dashboard is the **minimal** layout. Adding Unity Cup or Trackblazer chrome to it would be wrong, and a mockup that shows a team panel on a URA run is a factual error, not a design choice.

What it does have that the design must carry:

- **Three scripted Inspiration events** at fixed times (Junior start, Classic early April, Senior early April), each scaled by the chosen Legacy. A run-timeline marker with a known future date.
- **NPC friendship as a tracked resource.** Fan Fest needs 3 bars with Director Akikawa; A Super Successful Event needs max Akikawa friendship; Twinkle Monthly Special Issue needs max friendship with Reporter Etsuko Otonashi. Both NPCs begin *appearing in training* at scripted points (after 3 turns; Early July Junior). Friendship bars are therefore first-class run state, and the schema has nowhere to put them.
- **Fan thresholds at a completely different scale from race entry gates.** 60,000 / 70,000 / 120,000 Turf and 40,000 / 60,000 / 80,000 Dirt gate Unique Skill upgrades. `UMAMUSUME_REFERENCE.md` §1.2.6 records race *entry* gates of 350 to 25,000. These are two separate mechanics and a single "Fans: 1,943" readout serves neither well; the UI needs the next *event* threshold, not just the next *entry* gate.
- **Stamina floors by distance**, expressed as practical minimums with rank letters: Sprint ~350-450, Mile ~450-550, Medium ~600-700, Long ~700-800. Treat as hard prerequisites. This is the "recommended target" concept, now with numbers.
- **Summer Camp in both Classic and Senior**, 4 turns each, all facilities Lv5, and the camp *ends* with +5 to three random stats. Rest and Recreation restore both Energy and Mood during camp.

#### Unity Cup — a second resource layer on top

- **Facility level is a team property, not a personal one.** `G-F=1, E-D=2, C-B=3, A=4, S=5`. The same "Lvl 5" label means opposite things in URA (you ground it) and Unity Cup (your team is strong enough). A level chip that doesn't say *why* it is that level is misleading in one of the two scenarios.
- **Spirit Bursts need their own visual state machine**, and it is a **six**-state machine, not four: chargeable (white flame) → charged (meter full) → held (charged, deliberately untriggered) → normal burst spent → **Extreme chargeable** → Extreme spent. *"Each character only gets one Spirit Burst per career"* is still true of the **normal** burst, and each teammate gets exactly one **Extreme** burst after it (D-223). A spent teammate must therefore never render as permanently consumed or resettable — it renders as **one tier spent, one tier pending**.
- **Extreme (purple) Spirit Bursts are live on Global as of 2026-07-01**, so the state machine has a fifth, higher state, and it carries a hard consequence the design has been getting wrong: **"There is a 0% Failure Rate in the training facility where the Support has active ESB."** An Extreme burst standing up a risk warning on that facility is a false alarm the tool would be inventing. The facility's risk affordance must go quiet, not merely smaller.
- **The 2026-07-01 patch also made a stronger opponent team live** — an **Elite Team** can appear in the 4th Team Race round once the qualifying conditions are met, and losing rounds can now be retried by spending a Clock. Both are timeline events worth a marker, and the retry in particular means a logged loss is no longer necessarily final.
- **Special Training carries no Energy penalty anymore.** The pre-patch rule — bursts raising Energy cost on every facility except Wit — was removed on 2026-07-01, so a Unity Training preview must **not** show an energy deduction it no longer incurs. The Wit exception survives only as a **burst** bonus: a Wit-facility burst grants **+5 extra energy recovery**. (§6.15 gauge and the preview deltas are where this used to bite.)
- **Multi-uma bonus scales with headcount on the tile** (2 / 3 / 4 / **5** flames), and it is **gated**: your trainee gains bonus stats and SP **only from 2 flames upward** — a lone flame benefits that teammate alone, so a one-flame facility is not a trainee gain at all. Facility occupancy is information, not decoration, and the ≥2 threshold is a display state, not just a magnitude.
- **Team Races: 5 rounds at fixed calendar points**, each a 5-race card, one per distance, 1-3 racers per distance. Structurally different from goal races and must not share their calendar cell treatment.
- **Team Name selection around Junior Late September**, five options bound to linked characters, reward granted only on beating Team Zenith.
- **The scenario-skill ladder is white → gold** and its denominator is **normal + Extreme bursts combined**: 4-6 · 7-9 · 10-12 · 13+ → white Lv1 +10/+10, white Lv3 +20/+20, gold Lv1 +30/+30, **gold Lv3 +40/+40** (stat + SP). A progress-toward-reward display, variant chosen by the team's highest stat rank. ⚠️ This section formerly named the tiers "Burning / Ignited Spirit" off normal bursts only; that retired naming is quoted once, as the withdrawn claim, in the superseded table below — the live rule is the white/gold ladder. "Ignited Spirit" is specifically what an **Extreme** burst hints.
- **The guide records a genuine usability defect we can fix:** *"Skill hint icons are largely hidden by the Unity Training icon overlay on facilities — check every facility manually."* A tracker that surfaces a hidden skill hint is real value, not a reskin.
- **Racing outside team and goal races is actively bad here.** The opposite of Trackblazer. Any race advisory must be scenario-gated or it will give harmful advice in one of the two.

#### Trackblazer — a different game shape

- **No fixed race list — but racing is still mandatory.** The goal structure is Grade Points against deadlines, and objective 1 is the Debut race. **The Race Calendar panel does not apply**, because there is no prescribed fixture to render. What replaces it is a Grade Point progress meter with deadline markers, plus a race log that is not decorative: Grade Points derive from that log's tiers and placements, so it is required data, not a free-form note field.
- **The deadlines are Late December of each year, and surplus does not carry forward.** That pair of facts defines the meter's job: it is not a running total, it is a countdown against a per-year target. A Grade Point readout that never resets states the wrong thing, and banking points for the next phase is not a strategy available to the Trainer.
- **Shop Coins and an in-run shop.** Coins scale with placement, 100 for first. Items raise stats, set condition, restore Energy, raise bond. Unspent coins are worthless, so the UI should surface a balance with a "spend it" nudge.
- **The shop restocks every 6 turns.** This converts the spend-it nudge into a countdown with a real decision in it: saving coins for an item you want means risking it leaves the rotation before you can afford it. A balance without the restock timer loses information the Trainer is actively reasoning over.
- **Rival races carry a VS icon** and only an outright **1st place** yields the skill hint; out-placing the rival but losing to someone else is a draw and pays nothing. A "must-win" marker distinct from a goal race. **Rivals only appear for distances the trainee has C aptitude or better in**, so the number of rival opportunities a given run can contain is bounded by her aptitude array, and a UI that implies a rival race is available at a distance she is D-rated in is stating something impossible.
- **Secret character events do not fire.** Silence Suzuka's Runaway style is unobtainable. A trainee-selection warning, not a per-turn concern.
- **Fan farming is the scenario's strength**, which inverts the URA framing where fans are three discrete gates.

### Resolutions to the two blocking conflicts

Both were opened on 2026-09-27 as "needs an owner ruling". Both are now closed against primary sources dated after the files in question, so no ruling is required.

#### 1. Unity Cup caps — resolved, and the higher figures are live

`docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md`, written **after** the 2026-07-01 Global update (last updated 2026-07-20), gives the caps directly as `1300 / 1300 / 1300 / 1300 / 1800` and states in parentheses: *"This matches the 'future update' caps mentioned as pending in the original overview document — they are now confirmed active."* That is the exact reconciliation of the conflict: `02-unity-cup.md` was right when written and is now superseded by its own predicted future. The game data independently produces the same numbers.

`UMAMUSUME_REFERENCE.md` §1.3.4 is therefore correct as applied to Global today, despite its rows being flagged `STALE` — stale sourcing that happened to converge on the post-rework values.

**Consequence:** the 1,800 Wit denominator in `DESIGN.md` §6.22 and in the v7 and v8 mockups is **confirmed**, not unconfirmed. It no longer needs a caveat.

#### 2. Trackblazer status — resolved, and its unknowns are now filled in

Global release **2026-03-12**, confirmed three ways: the dataset's `start_en`, `UMAMUSUME_REFERENCE.md` §1.6 item 3, and both owner-supplied Trackblazer files. `docs/scenarios/03-trackblazer.md`'s "What We Don't Know Yet" section is now largely answered by `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` section "Trackblazer (uma.guide)" (strategy, epithets, shop ratings) and section "Trackblazer (GameTora)" (patch-accurate mechanics and the full item list):

| Fact | Value | Source |
|---|---|---|
| Grade Points per 1st place | G1 100 · G2 80 · G3 60 · OP 40 · Pre-OP 20 | 05, 04 (agree) |
| Grade Point placement modifier | 1st 100% · 2nd 60% · 3rd 40% · 4th-5th 20% · 6th+ 10% | 04 |
| Shop Coins by placement | 1st 100 · 2nd-3rd 60 · 4th-5th 30 · 6th+ 0 — **independent of race grade** | 05, 04 (agree) |
| Grade Point objectives | 4 total: Debut race, then 60 / +300 / +300 | 05 |
| Deadline timing | End of Junior, Classic, Senior year | 04 |
| Aptitude exception | High-dirt/low-turf: obj 2 drops to **30**, obj 3 to **200**. Poor non-short aptitude (e.g. Curren Chan): obj 3 = **200** | 05 |
| Surplus carry-over | **None** — each objective period starts at zero | 05, 04 |
| Shop restock | Lineup refreshes **every 6 turns**, with a visible in-game timer | 05 |
| Shop stock limit | **Max 5 copies** of a single item held at once | 05 |
| Shop special offers | Limited items (own window, flagged top-right) · Sales 10-20% off (flagged top-left) | 05 |
| Rival appearance | Random, from **Early August, Junior Year**; red/blue VS speech-bubble icon | 04, 05 |
| Rival reward | Win = a skill hint (05) or a hint **or** +5 to 2 random stats (04). Participating alone can add shop items | 04, 05 — minor conflict, see below |
| Facility leveling | Repeat one stat 4x, **plus** permanent +1 from shop Training Application items (150 coins) | 05 |
| Scenario Link character | **None.** No new story characters either | 05 |
| Finale | **Twinkle Star Climax**: 3-race points league, not an elimination bracket | 05, 04 |
| Secret events | Cannot trigger | 05, 04 |

The one internal conflict worth recording: umamusu.wiki quoted Grade Point thresholds of 100 / 450 / 480, which does not match the 60 / 300 / 300 both owner files agree on. Two agreeing primary files outrank it; treat the wiki figures as likely cumulative.

#### The trap that produced the wrong Trackblazer row

`UMAMUSUME_REFERENCE.md` line 357 recorded the Climax row as `1200 / 1900 / 1200 / 1500 / 1200` and attributed it to **Game8 JP 2025-11-21** and **Kamigame 2024-02-19**. Climax is JP's name for the scenario Global shipped as Trackblazer (§1.6 item 3). The game data says `1200 / 1900 / 1200 / 1200 / 1500`, so **the reference doc had Guts and Wit transposed**, and `ADR-0002` plus `DESIGN.md` §6.22 faithfully carried that error forward under a Global label. Line 357 was corrected on 2026-09-27; the correction landed in §1.6's Trackblazer row first, which is why the source table stayed wrong for a round after the diagnosis was written — see D-285.

Stamina 1900 agrees either way, which is what kept it invisible: the one number large enough to break validation was right, and only the two below it were swapped.

Two lessons, and the second is the one that generalises:
1. A stale row propagates silently as long as it stays plausible.
2. **Translating a scenario's name is not re-sourcing its numbers.** Any value carried across the JP/Global boundary needs a Global-side source, not a renamed label. `UMAMUSUME_REFERENCE.md` §6 exists to catch this class and did not.

#### The same trap in the other direction

`docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` states Global Trackblazer is *"expected to launch with the standard 1200/1200/1200/1200/1200 caps"* and warns against assuming the higher Stamina/Wit caps. That file is dated **2026-03-12, the launch day**, and hedges explicitly ("expected", "may be adjusted post-launch", "check current patch notes"). The data says it was adjusted. So a source that was correct-then-stale on its own publication date is now wrong, and its own advice was the load-bearing line. A file's authority is its date, not its publisher.

### Live Global stat caps — measured from the game's own data

Resolved with a **machine-readable source** rather than prose, because the guide files disagreed with each other. GameTora serves the scenario table as plain JSON; manifest key `scenarios`, hash `61b7c51c`, fetched 2026-09-27. Each row carries a five-element `stats` array and a `hard_caps` array.

**`stats` is a bonus over a 1200 base, not a ceiling.** Two independent proofs: every row reproduces its published caps when added to 1200, and JP scenario id=8 carries a **negative** Stamina bonus of `-200`, giving a cap of **1000** — below the base. A field that can sit under the base is a delta, not a limit.

`hard_caps` is a separate, higher engine ceiling: **2000** for every scenario live on Global, 2500 for the two JP-only future ones. Its sixth element (`9999` / `99999`) has unexplained semantics; do not assume it is a stat.

| Order | Scenario | Global live | Speed | Stamina | Power | Guts | Wit | Hard cap |
|---|---|---|---|---|---|---|---|---|
| 1 | URA Finale | 2025-06-26 | 1400 | 1400 | 1400 | 1400 | 1400 | 2000 |
| 2 | Unity Cup | 2025-11-06 | 1300 | 1300 | 1300 | 1300 | **1800** | 2000 |
| 3 | Trackblazer | 2026-03-12 | 1200 | **1900** | 1200 | 1200 | 1500 | 2000 |
| 4 | Our Grand Concert | 2026-07-22 | 1600 | 1300 | 1300 | 1500 | 1300 | 2000 |

Three prose sources cross-check the arithmetic exactly: Game8's Global URA guide ("+200 stat caps" → 1400), Game8's Global Unity Cup guide ("+100 … +600 Wit" → 1300/1800), and the caps table in `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` section "Unity Cup (GameTora)".

**A fifth finding hiding in that table: Our Grand Concert has been live on Global since 2026-07-22.** It is the fourth permanent scenario and it caps Speed at **1600** — the highest Speed ceiling on Global, above URA's 1400. `UMAMUSUME_REFERENCE.md` §1.6 records it and states **"4 total selectable"** on Global, so the repo knew; what is missing is a mechanics guide, and none of `docs/scenarios/` covers it. Every scenario-composition rule in this document is therefore a **three-of-four** analysis, and D-220 and D-233 in particular must be written so a fourth scenario can be added without redesigning the strip or the finale panel.

**The ceiling a Trainer can legitimately reach today is 1,900** (Trackblazer Stamina). `PRD.md` FR-C-2 validates `TurnEntry` stats at **0..1200**, so since 2026-07-01 any Global player who pushed a stat past 1200 has been unable to log it. The stored data also answers what to widen the bound to: not a guessed round number but the game's own `hard_caps` = **2000**. See `ADR-0002`.

The 1200 line still matters as a *displayed* value, because it is the halved-gains threshold — a game mechanic, not an app limit.

### Mechanics the 2026-07-01 patch superseded

`02-unity-cup.md` predates the update and says so of several features; `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` section "Unity Cup (GameTora)" is written against it and states plainly that earlier guides, *"including the original overview document in this set"*, carry wrong Spirit Burst values. Reading the two side by side invalidated rules I had already written into `CONSTRAINTS.md`, not only the owner's file. These are the ones that change UI behaviour.

| Claim as designed | Post-patch fact | What the UI must do differently |
|---|---|---|
| Spirit Bursts raise Energy cost on every facility except Wit (my **D-223**) | *"the additional energy cost was removed from Special Training"* | Do not add an energy penalty to a Unity Training preview. The Wit exception survives only as a **burst** bonus: a Wit burst grants **+5 extra energy recovery**. |
| One Spirit Burst per character per career; a spent teammate is permanently consumed (my **D-223**) | Extreme Spirit Burst is available on a teammate's **next** Unity Training after their normal burst fires | A spent teammate is **not** terminal. They enter an Extreme-chargeable state. Rendering "spent" as a dead end is now wrong, and it was the load-bearing idea of the four-state machine. |
| Bursts grant a **random** skill hint (both owner files) | Hints now draw from that support card's own hint pool; falls back to A-rank aptitude when the pool is exhausted | The tool can name *which pool* a hint came from. It still must not predict which hint — that is a draw, and Planner Rule 1 forbids simulating it. |
| Burst ladder: 13+ → Burning Spirit X Lv.3 + 20 SP, and 02's "Burning/Ignited" naming | Rewards now scale on **bursts + Extreme bursts combined**: 4-6 white Lv.1 +10 stat +10 SP · 7-9 white Lv.3 +20/+20 · 10-12 gold Lv.1 +30/+30 · 13+ gold Lv.3 +40/+40 | The progress meter's denominator changed meaning, not just its numbers. Count both burst tiers in one total. |
| Team Rank tops out at S (my **D-222**) | **S+** exists above S | One more rung. Facility level still caps at 5 at S, so rank and facility level are now decoupled at the top — S+ grants a *second* "It's On!" hint rather than a higher facility. |
| No league-rank concept | League rank rises with opponent strength and **falls on a loss**; Elite Teams need rank ≥10, Team Rank ≥A, and ≥1 Extreme Burst | A new tracked resource with a downside, and a gating condition for the Elite Team path. The client shows a circle-based win-odds estimate with a "3 circles" rule of thumb. |
| NPC friendship is a universal run resource (my **D-226**) | **Akikawa is absent from Unity Cup**; Riko Kashimoto stands in, so Unity Cup's April Unique Skill level-up has **no bond condition** | Which NPCs exist at all is scenario-dependent. A friendship gauge for an NPC who is not in the scenario is worse than no gauge. |
| A logged loss is final | Team Race losses can be **retried with an Alarm Clock** | The race log must allow a loss to be superseded, and should say so rather than silently overwriting. |
| Unity Cup teammates' stats track the support card's level | Teammate stats are raised **only** through Special Training and are *"NOT dependent on the support card's own level or limit break"* | Team composition display must not imply card level drives team rank. Only real Support Cards carry a bond gauge or can do Friendship Training; story and random teammates cannot. |
| Finals are the URA-shaped elimination races | Unity Cup finals come **after 4 Team Races** vs Team Zenith; beating Zenith at Rank S puts **Little Cocon and Bitter Glasse** into the later URA-style final race as opponents | The end-of-run sequence is scenario-specific and introduces two named original characters the design has never accounted for. |

#### Trackblazer's own structural corrections

Beyond filling in unknowns, three things in `04`/`05` contradict what this document and `CONSTRAINTS.md` had asserted:

- **There is no Scenario Link character and no new story character in Trackblazer.** My **D-216** stated *"Each scenario has a Scenario Link character."* That is false. The dataset confirms it independently: Trackblazer's `scenario_linked_characters` is an empty array, while URA has Aoi Kiryuin and Unity Cup has five entries. A scenario-link affordance that assumes one exists per scenario breaks on the third scenario.
- **Facility level is documented, not unknown.** My matrix said "Unknown / not documented" for Trackblazer. It follows the URA rule — repeat one stat 4x, max Level 5 — **plus** permanent +1 purchases from the shop at 150 coins. That makes Trackblazer the only scenario where facility level is partly *bought*, which is a shop consequence the UI should surface.
- **The finale is a points league, not an elimination bracket.** Twinkle Star Climax is 3 races scored by Victory Points (1st 10 · 2nd 8 · 3rd 6 · 4th 4 · 5th-6th 3 · 7th-9th 2 · 10th-13th 1 · 14th+ 0), max 30, and you win on total points rather than by winning all three. Climax races **pay no Shop Coins**, so the run's final economy decision is spending down beforehand. An end-of-career panel modelled on URA's qualifier → semifinal → final progression is wrong for this scenario.

#### A terminology hazard in the source files themselves

These three files are written in the wikis' English, which is **not** the Global client's English, and they carry two banned terms in table headings:

- **"Wisdom"** appears throughout `05` and `06` for the stat Global calls **Wit** — including in facility rows and `hard_caps`-adjacent tables.
- **"Motivation"** appears in `05`'s shop item table (`Motivation +1`, `Motivation +2`) for what the client calls **Mood**.

Both are exactly the class `CONSTRAINTS.md` D-20 exists to stop. Copying a table out of these files into UI copy would import a wrong term wholesale. The numbers are trustworthy; the headings are not.

#### The Race Fatigue table, and what it does to ADR-0001

`ADR-0001` concluded that training failure **percentages** could not be sourced and must ship as bands. `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` section "Trackblazer (uma.guide)" contains a fully quantified probability table for a different failure class — Race Fatigue, scaling with consecutive races:

| Consecutive races | Mood down (1+ Energy) | Mood down (0 Energy) | Skin Outbreak (1+E) | Skin Outbreak (0E) | 3 random stats −10 |
|---|---|---|---|---|---|
| 1 | 0% | 15% | 0% | 4% | 0% |
| 2 | 0% | 33% | 0% | 8% | 0% |
| 3 | 60% | 90%+ | 15% | 25% | 0% |
| 4+ | 100% | 100% | 33% | 33% | 40% |

**Sourcing status of that table: one guide, and the `+` cells are lower bounds.** Every figure comes from `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` section "Gameplay Flow & Race Fatigue" alone — no second source, no client capture, no dataset field — and `| 3 | … 90%+ |` means *at least* 90%, not 90%. So the table is **provisional** under `CONSTRAINTS.md` D-230, which now requires client data or two independent sources before a percentage prints as a number; a UI rendering `90%` for that cell states a figure no source gives. What is safe today is the shape: risk rises with consecutive races, it reaches certainty at four, and it cannot occur after Late December.

Two rules follow. **The risk is a function of a count the tool already keeps** — consecutive races — so this is the rare case where a real probability can be shown instead of a band, derived deterministically from entered turns exactly as Planner Rule 4 requires. And **Race Fatigue cannot occur after Late December**, so the warning must switch off in the endgame stretch rather than following the player into the Climax.

#### 3. Mood race-performance figure

`01-ura-finale.md` gives Great Mood as `+20% training, +4% race performance`. `UMAMUSUME_REFERENCE.md` §1.1.6 gives 絶好調 as `+20% training, +10% pre-race base ability`. The training figure agrees across both; the race figure does not. Neither is UI-blocking today because the tool displays the tier word, not the multiplier.

#### 4. Terminology: Recreation vs Outing

`01-ura-finale.md` uses **Recreation** for お出かけ. `UMAMUSUME_REFERENCE.md` §1.1.5 uses **Outings**. Same action, two English terms, neither verified as the Global client string. D-20 blocks promoting either. Unresolved.

### What this does to the schema

`ADR-0003` proposed `scenario_races` and `race_entries`. That design is **URA/Unity-Cup-shaped** — but not for the reason this file first gave. It said Trackblazer "has no race goals", which is wrong: objective 1 is the Debut race, and every Grade Point comes from a race. The real mismatch is that `scenario_races` models a **calendar the Trainer follows**, and Trackblazer has no prescribed calendar — the Trainer chooses entries and the deadline is a *points total*, not a named race. So Trackblazer still needs per-race rows (entry, tier, placement, points earned), and what it does not need is a locked fixture list. Two options:

- **Generalise** `scenario_races` into `scenario_slots` with a `kind` discriminator (`GoalRace`, `TeamRace`, `GradeDeadline`, `ScriptedEvent`), letting each scenario populate different slot types against one table.
- **Split**, giving Trackblazer its own `grade_deadlines` and leaving `scenario_races` for the goal-race scenarios.

Generalising is the smaller schema and the one that keeps a single timeline view working across scenarios, which matters because the timeline is the product. Recommend generalising.

Also newly required by the URA material and not in ADR-0003: **NPC friendship state** (which NPC, how many bars) and **Spirit Burst state** (per teammate, spent or not). Both are per-run, per-turn facts a Trainer would want logged, and both are scenario-specific, which is the argument for storing them as a typed json payload on `turn_events` rather than as columns on `turn_entries`.

### Design rules this generates

Recorded in `docs/design-research/CONSTRAINTS.md` as D-220 to D-229.


---

## RAW-FINDINGS.md

RAW-FINDINGS: visual research for Umamusume Trainer Companion


Phase 1 output. Research and documentation only; no production code in this session.
Compiled 2026-09-27. Every colour value below was measured from a real file in this repository, not chosen.

Sources of evidence:
- `docs/game-screenshots/` (1,160 PNG frames, captured 2026-07-14 to 2026-07-18)
- `docs/UMAMUSUME_REFERENCE.md` §1.1, §1.3, §6 (Global client terminology)
- `PRD.md`, `ARCHITECTURE.md`, `ARCHITECTURE-ESSENTIALS.md` (data model the UI must map to)
- Official web properties, section 6 below

Scripts and intermediate data live in `docs/design-research/_scratch/` (untracked scratch): `triage.py`, `cluster.py`, `sheet.py`, `probe.py`, `probe2.py`, `accents.py`, plus `clusters.json`, `accents.json`, `colorprobes.json`, `colorprobes2.json`.

---

### 1. Corpus triage

#### 1.1 What the filenames tell us (and what they don't)

All 1,160 files are named `Screenshot YYYY-MM-DD HHMMSS.png`. There is **no semantic naming**, so category assignment had to come from image content, not from the path. The corpus is Windows Snipping Tool output: several frames visibly include the "Screenshot copied to clipboard / Snipping Tool" toast, which explains the frame-size spread.

Capture sessions, by date:

| Date | Frames | Character of the session |
|---|---|---|
| 2026-07-14 | 347 | Training runs, story events, choice panels |
| 2026-07-15 | 639 | Longest session; dense event/choice capture |
| 2026-07-17 | 77 | Full-desktop frames: menus, modals, calendar |
| 2026-07-18 | 97 | Full-desktop frames: race results, career profile |

#### 1.2 Frame taxonomy by dimensions

Frame size is the cheapest reliable proxy for capture intent, and it splits the corpus three ways:

| Bucket | Frames | Distinct after dedupe | What it is |
|---|---|---|---|
| `1920x1080` full desktop | 173 | 44 | Two-window captures: game client at half width plus a side panel. **Highest design value** (shows chrome, layout, and panel relationships together). |
| `~1200-1570 x 470-1080` | 96 reps in the wide set | 96 | Large region snips: event scenes with the Choices panel, race screens. |
| `~560-880 x 250-1080` | ~891 | ~611 | Portrait region snips, mostly the **training HUD** and small modals. |

Perceptual dedupe (dHash, Hamming threshold 6, 60-frame rolling window) collapsed **1,160 frames into 751 distinct screens**. The largest single cluster holds 58 frames (`Screenshot 2026-07-18 001811.png`) and the next 14, 14, 7, 7, 6, 6: the corpus is heavily redundant, which is why sampling beats enumeration.

#### 1.3 Global luminance finding

Median frame luminance is **193 of 255** (min 86, max 246). The dominant-colour pass over the UI-heavy frames returns panel whites in the `#F5F3F8`–`#FAF9FC` range covering 40-60% of the frame.

**This is the single most load-bearing research result in this document.** The instinct for a "game UI" is a dark, glassy, neon-trimmed surface. Umamusume's client is the opposite: a **light, near-white, high-key interface** with saturated accent colour used sparingly and semantically. A dark-mode-first design would be unrecognisable as this game. See §7.1 for the consequence.

---

### 2. Screen inventory

Categories found in the corpus, with the representative frame to cite for each.

| # | Category | Representative frame | What it contributes to the design system |
|---|---|---|---|
| S1 | **Training HUD** (turn state) | `Screenshot 2026-07-14 194819.png`, `.../2026-07-14 202142.png` | Turn counter, date strip, Goal row, Energy gauge, Mood pill, discipline banner, six-column stat bar with grade badges and gain previews, five circular discipline buttons, support-card rail with bond gauges |
| S2 | **Event / Choices panel** | `Screenshot 2026-07-14 124128.png`, `.../124926.png` | The decision-card pattern: in-scene banner buttons plus a right-hand preview list showing each option's outcome before commitment |
| S3 | **Log panel** (turn history) | `Screenshot 2026-07-17 234521.png` | Chronological card stack with avatar overhang, per-entry divider, sign-coloured prose, collapsible phase header |
| S4 | **Skill acquisition ("Learn")** | `Screenshot 2026-07-17 234521.png` | Skill row anatomy: icon, name, description, distance tag, Hint Lvl discount badge, cost stepper, gold selected state, Confirm/Reset pair |
| S5 | **Umamusume Details modal** | `Screenshot 2026-07-17 232345.png` | Modal header grammar, stat band, aptitude table (Track / Distance / Style), Growth Rate pills, segmented tab control, skill chip grid |
| S6 | **Scheduled Races calendar** | `Screenshot 2026-07-17 230755.png` | Three-segment year tab bar, 4-column date cell grid with disabled / available / current states, race thumbnail with Goal and Scheduled flags |
| S7 | **Right navigation rail** | `Screenshot 2026-07-17 230755.png`, `.../234521.png` | Vertical icon-over-label rail, dashed separators, active state = the whole cell becomes a saturated tile |
| S8 | **Team Showdown / opponent select** | `Screenshot 2026-07-17 235229.png` | Rank badge with laurel wreath, italic gradient display numerals, team-name colour band, green selection brackets |
| S9 | **Race result / live** | `Screenshot 2026-07-18 000919.png`, `.../235511.png` | Dark leaderboard overlay (the one genuinely dark surface in the client), "RACE FINISHED" display type |
| S10 | **Photo Album / gallery** | `Screenshot 2026-07-18 134106.png` | Thumbnail rail with pill label overlay, selected = bright green outline + glow, notch-cut Back button |
| S11 | **Sparks / Career Profile** | `Screenshot 2026-07-14 122331.png`, `.../233057.png` | Colour-coded type badges, dense card grids |
| S12 | **System dialogs** | `Screenshot 2026-07-14 025344.png`, `.../211146.png` | Small confirm modals, Close button grammar |

Not present in the corpus: any gacha/Scouts screen, any support-card composition screen, and any settings screen. Where the design needs those, it borrows S4/S5 grammar rather than inventing a new one.

---

### 3. Visual language extraction

#### 3.1 Surface and material

The client is **not** glassmorphic. Measured properties:

- Panels are **opaque near-white**, `#F7F6FA` to `#FAF9FC`, with no backdrop blur over the scene except where the game deliberately dims the whole frame behind a modal (S5).
- Edges are defined by **form, not by border weight**. Where a border exists it is thin (1-2 px) and low-contrast: a pale grey-violet on white cards, or a saturated colour only on an interactive element.
- Depth comes from **stacked silhouettes**, not from shadow spread. The discipline banner (S1) is two ribbons at different offsets; the choice button (S2) is a white body with a coloured arrow-cap; the stat bar is a coloured header band sitting on a white body. Each component is built from 2-4 overlapping flat shapes.
- The **page background** is a pale lavender-white carrying a low-poly faceted diamond pattern (clearly visible behind the Scheduled Races panel in `230755` and `235229`). It is a texture, not a gradient, and it reads as soft crystalline paper.
- Glossy highlight: buttons and pills carry a **hard-edged diagonal sheen** in their upper-left, visible on the Close button (S5), the Save button (S10), and the Confirm button (S4). This is a stylised plastic/enamel sheen, not a physical material simulation.

#### 3.2 Colour, measured

Values below are 5x5 block averages at the named coordinate, or saturation-filtered cluster peaks where the target is a small element. Cross-checked across frames; a value appearing in three frames is marked "corroborated".

**Core action green** (the brand colour of the client):

| Token | Measured | Where |
|---|---|---|
| Green, action face | `#7ECC09` | Log header capsule, `234521` at (1310,40) |
| Green, action face | `#83CF0A` | Modal header bar, `232345` at (700,48) |
| Green, segment active | `#85D008` | Skills tab, `232345` at (743,537) |
| Green, cluster peak | `#72C800` / `#76C900` / `#71C600` | event-choices, result-pits, team-showdown frames (corroborated) |
| Green, dark lattice | `#6ABE01` | Log header argyle, `234521` at (1075,40) |
| Green, discipline banner | `#52C518` / `#55C618` | `194819`, `202142` |
| Green, confirm gradient top | `#658A04` | Confirm button, `234521` at (533,895) |
| Green, confirm gradient bottom | `#376708` | Confirm button, `234521` at (533,940) |
| Green, pale tint | `#CAF0A2` | Phase group bar, `234521` |
| Green, disabled/desaturated | `#DDF2BE` | Scheduled Races header when the panel is behind a modal, `235229` |

The action green is a **yellow-green at hue ~87°**, not a teal-green. That hue position is a signature; drifting it toward `#22C55E` (Tailwind's default green-500) loses the game instantly.

**Accents:**

| Role | Measured | Where |
|---|---|---|
| Info blue, saturated | `#0084DD` / `#0088E0` / `#007BDE` | cluster peaks, corroborated across 3 frames |
| Info blue, mid | `#4EA1E8` | cluster mean |
| Turn-card numeral blue | `#18ACFF` | `194819` at (100,19) region |
| Skill-point cyan | `#009FE1` / `#03B3E2` | cluster peaks |
| Skill-point cyan, pale | `#8DEDFF` | Skill Pts header, `202142` |
| Mood GREAT pink | `#FB5590` | `202142` at (560,121) |
| Mood GOOD orange | `#ED8036` | `194819` at (560,130) |
| Discount / highlight orange | `#FF9A2C` | Hint Lvl badge, `234521` at (740,417) |
| Gold, selected card | `#ECD38D` body, `#FFFCE1` sheen | selected skill row, `234521` |
| Gold, peak | `#FFA100` / `#E9B600` | cluster peaks |
| Indigo, grade badge | `#351F70` / `#311D70` | cluster peaks |
| Crimson, deep | `#800014` / `#80001A` | cluster peaks (used for negative/emphasis, not for error) |
| Grade D badge blue | `#6DA0D9` | `202142` |

**Neutrals:**

| Role | Measured |
|---|---|
| Panel surface | `#F7F6FA` – `#FAF9FC` (dominant-colour pass, 40-60% coverage on UI frames) |
| Panel surface, pure | `#FFFFFF` (12.8% of the event-choices frame) |
| Card idle lavender-grey | `#D2D2DB` → `#E7E7EC` (skill rows, `234521`) |
| Cost stepper minus grey | `#969299` |
| Disabled cell grey | `#D0D1D0` (unavailable date cell, `230755`) |
| **Ink / body text brown** | `#6A5641` at 18.9% coverage (event-choices frame); cluster mean `#725534`, peak `#6A2B00` |
| Ink, heading dark | `#482720` (11.8% of the event-choices frame) |

**The ink is brown, not black.** `#6A5641` for body and `#482720` for emphasis. This is the second most load-bearing finding: pure `#111827` or `#000` text on these panels reads as a different product. A warm dark brown at high coverage is what makes the light UI feel friendly rather than clinical.

**Semantic sign colouring is inverted from web convention.** Measured in two independent places:
- Log entry (`234521`): "Energy went **down** by 5" renders *down* in blue; "Guts went **up** by 9" renders *up* in orange.
- Choice preview (`124128`): "Friendship with Bamboo Memory **+5**" and "Max Energy **+4**" in orange; "Energy **-5**" in blue.

So: **increase = orange/gold, decrease = blue.** Green is reserved for action and affordability, never for "good delta". Red is reserved for the training-failure warning. Adopting the web's green-up/red-down default would break the resemblance to the client. See DESIGN.md for how this is tokenised.

#### 3.3 Typography

Font files are not extractable from screenshots, so this is a shape reading, not a family identification. Section 6 supplies the measured web-font answer for the official sites.

- **Body and labels**: a rounded humanist sans. Terminals are open and soft; the lowercase `a` is double-storey; counters are wide. It is close in personality to a rounded grotesque, and it is used at a single medium weight for most UI text.
- **Numerals are the display voice.** Turn counts, stat values, costs, and rank numbers are set much larger and heavier than their surrounding labels, and the big ones (Rank 19, the `328`/`329`/`279` stat row) carry a **gradient fill plus a thin dark outline**. Numbers, not headlines, are what the eye lands on.
- **Headers inside panels** are set at only a modest step above body size and are centred within a coloured capsule (Log, Scheduled Races, Choices, Sparks). There is no large page title anywhere in the client.
- **Italic display forms**: "Rank 19", "RACE FINISHED", "Received Result Pts!" are italic, heavy, and gradient-filled with an outline and a drop shadow. This is the celebratory register and it is used rarely.
- **Japanese text** sits alongside Latin in the JP client at the same scale; the Global client drops it. Our tool shows both (`name`, `name_ja`), so the type system must keep a CJK-capable fallback that does not change the Latin metrics.
- Letter-spacing on Latin labels is slightly open; `text-transform` is never used to shout. Even section headers are sentence case ("Scheduled Races", "Umamusume Details", "Skill Points").

#### 3.4 Shape and radius grammar

| Element | Radius | Notes |
|---|---|---|
| Panel / modal | ~10-14 px, uniform | Modals are rounded on all four corners including the coloured header |
| Section capsule header | fully rounded (pill) | Log, Scheduled Races, Choices |
| Buttons (rectangular) | ~10-12 px | Confirm, Reset, Close, Details |
| Stat bar columns | ~6-8 px, tight | smaller radius than the panel it sits in |
| Skill row / log card | ~8-10 px | |
| Discipline buttons | **circle** | the only fully round interactive elements in the HUD |
| Grade badge | ~4-6 px square | letter sits inside a small rounded square |

The **cut-corner and arrow-cap silhouette** is the other half of the shape language and it is not achievable with `border-radius` alone:
- Choice buttons end in a **right-pointing arrow cap** filled with a gradient of the accent (`124128`, the green and gold variants).
- The discipline banner and the "Support Card Event" tag are **parallelograms with one slanted end**.
- The Back button is a rounded rect with a **notch cut into its left edge** (`134106`).
- The selected choice preview carries a **double slash** (two thin angled stripes) at its right end.
- The Confirm/Save buttons carry a **hard diagonal sheen** across the upper-left.

#### 3.5 Depth and shadow

- Cards on the pale background carry a **close, soft, cool shadow** (roughly a 0 2-6 px blur at low alpha with a blue-violet cast, not a grey one). It lifts the card without separating it from the page.
- Stacked ribbons create depth by **offset and overlap**, with the lower ribbon slightly darker.
- The selected skill row and the selected photo filter use a **coloured outer glow** rather than a bigger shadow (`134106` shows a bright green ring plus bloom on the selected thumbnail).
- There is no glassmorphism, no inner shadow, and no neumorphism anywhere in the corpus.

#### 3.6 Iconography

- Discipline icons are **small 3D renders** (running shoe, heart, dumbbell, horn, book) sitting inside a flat coloured shape. They are glossy and miniature, not line icons. A line-icon set placed next to them reads as a different product.
- The five stat icons double as the stat's identity across the whole UI: shoe / heart / dumbbell / flame-horn / book, and the same glyphs head the stat band, the growth-rate pills, and the discipline buttons.
- Rarity and rank are expressed with **stars** (`★★★` with hollow unfilled slots) and **laurel-wreath letter badges**.
- The `!` in a small circle marks an info affordance; it appears top-centre on the scene header and next to the turn counter.
- A **double chevron** (`»` rotated to point down, in yellow) marks "this is the boosted option", under the active discipline button and on the support-card rail.
- A small **flame** badge marks a support card sitting on its own training tile; a **rainbow arc** marks friendship training available.

#### 3.7 Motifs

Recurring decorative vocabulary, all of it present in the frames rather than inferred:

1. **Argyle / diamond lattice** in the left end of green capsule headers (`234521`, `235229`, `232345`). Measured as a darker-green diamond grid inside a `#6ABE01` field.
2. **Low-poly faceted page background** in pale lavender.
3. **Sparkle/star burst** on skill icons and on the acquired-skill state (the blue four-point sparkle in S4).
4. **Laurel wreath** around rank letters.
5. **Ribbon and banner forms** with slanted ends, used for anything that names a thing (event title, discipline, phase).
6. **Double slash** marking a selected or active row.
7. **Number-cloth stripes** on the calendar tab bar.
8. **Radial glow** behind the Umamusume portrait.

#### 3.8 Layout grammar

- **Two-window desktop composition.** The client is used on PC with a scene panel at roughly 50% width and a working panel beside it. Every full-desktop frame in the corpus is exactly this. Our tool is a desktop-only web app (PRD §2), so this is a directly transferable layout fact, not a metaphor.
- **Persistent right rail** for top-level destinations, icon over label, dashed separators, active = saturated tile.
- **Header capsule centred over the panel body**, with the lattice ornament bleeding from the left edge.
- **Segmented pill tab bar** for peer views (Junior / Classic / Senior Year; Conditions / Skills).
- **Dense 4-column card grids** for time-series data (the race calendar is a 24-cell grid of half-months).
- **Stat readouts are a horizontal band of equal columns**, each column stacked as: label / grade badge + value / cap. Six columns including Skill Pts, which is visually separated by its own teal header.
- Gains are shown **above** the number they will change, in a cloud-shaped bubble, not inline and not in a tooltip.

---

### 4. Component anatomy, as observed

#### 4.1 Turn indicator (S1)
A torn-page calendar card: white body, blue outline, a blue tab strip across the top with two punch holes, the remaining-turn numeral in large blue, and "turn(s) left" set in small text beside it. A second, smaller card of the same shape stacks underneath for the phase countdown ("8 turn(s) Until the Unity Cup"). This is the strongest "where am I in the run" affordance in the client and it is a *card*, not a progress bar.

#### 4.2 Energy gauge (S1)
A fully rounded track with a dark charcoal unfilled remainder and a **segmented** fill running cyan → green → olive, so the bar reads both level and threshold at once. Label "Energy" sits in its own small cream pill to the left of the track.

#### 4.3 Mood pill (S1, plus the owner's Mood Effect capture)
A saturated rounded pill with a directional arrow glyph and a single uppercase word. Measured strings, all five, with their training and pre-race effects and their arrow:

| Word | Arrow | Training | Pre-race | Pill colour |
|---|---|---|---|---|
| `GREAT` | up | +20% | +4% | pink `#FB5590`, point probe on frame `202142` |
| `GOOD` | up | +10% | +2% | orange `#ED8036`, point probe on frame `194819` |
| `NORMAL` | flat | 0% | 0% | not captured |
| `BAD` | down | −10% | −2% | not captured |
| `AWFUL` | down | −20% | −4% | not captured |

The words and the numbers come from an owner-supplied capture of the client's Mood Effect panel (2026-09-27) — the client's own legend screen, which lists all five tiers and marks the current one. That is outside this document's screenshot corpus, so it is attributed to the owner rather than to a frame ID, and it is what closed the terminology gap that D-20 and D-203 recorded as blocked. It also retires the JP guides' English glosses (Peak / Good / Normal / Poor / Worst) and their ±10% / ±5% pre-race column, both of which `UMAMUSUME_REFERENCE.md` §1.1.6 carried until this capture arrived.

The three uncaptured colours are a deliberate non-claim: the Mood Effect panel is a legend, and averaging its row bands measures the pill against the panel field rather than the pill fill (the same method returns a dark crimson for the `GREAT` band where the HUD point probe measures `#FB5590`). `DESIGN.md` §3.5 therefore *derives* those three at the anchors' luminance and marks them provisional, and `CONSTRAINTS.md` D-259 makes the arrow mandatory precisely because equal-luminance steps cannot be ordered by hue.

The pill carries colour, word and arrow; never colour alone. Of the five arrows the four off-centre ones read unambiguously in the capture; the `NORMAL` mark is recorded as neutral, and a flat arrow versus no arrow at that row is the distinction worth settling on the next capture (D-259).

#### 4.4 Stat band (S1, S5)
Six equal columns. Each: a coloured header strip with icon + label, then a white body holding a grade badge, a large value, and a small `/cap` beneath. Skill Pts is separated by a teal header and has no grade badge. Gain previews float **above** the band in scalloped white bubbles with red numerals, plus a larger orange delta under the bubble.

#### 4.5 Choice card (S2)
Two-part object. The **button** is a white body with a thin accent outline and a gradient arrow-cap at the right end, an accent-coloured circular icon at the left, and brown bold label text. The **preview** sits in the side panel: unselected it is a white card listing each outcome as brown prose with orange/blue deltas; selected it becomes a solid action-green banner with white text and a double slash. Selecting does not commit; a separate Confirm does.

#### 4.6 Log entry (S3)
White rounded card. A circular avatar **overhangs** the top-left corner, outside the card's box. Inside: name in brown bold, a hairline rule, then body prose. Deltas are written as sentences with the direction word colour-coded. Consecutive entries are grouped under a **collapsible green phase bar** carrying a scenario logo and a circular chevron.

#### 4.7 Skill row (S4)
Rounded rectangle, idle fill a lavender-grey vertical gradient. Left: square icon with its own rounded frame. Centre: name (brown bold) then description (smaller brown) ending in a parenthesised distance tag. Right-top: an orange discount badge reading "Hint Lvl N / NN% OFF!". Right-bottom: a cost stepper of grey minus, white numeral, green plus. Selected state swaps the whole row to a gold gradient.

#### 4.8 Modal (S5)
Rounded white sheet over a dimmed scene. Full-width green header capsule with lattice bleed and a centred white title. Body is stacked sections separated by hairlines. Footer has a single centred Close button, white with a thin dark outline and a diagonal sheen.

#### 4.9 Date cell (S6)
Rounded rect in one of three measured states: unavailable `#D0D1D0` with a muted plus; available white with a green plus; current pale yellow with a green plus and a warm outline. A race occupying the cell replaces the plus with a small artwork thumbnail wearing a pink `Scheduled` or red `Goal` flag.

---

### 5. Interaction and state patterns worth carrying

1. **Preview before commit.** Every consequential choice shows its outcome numbers before it is accepted, and acceptance is a separate action. This is the core of the "Guided/Contextual Input" brief and it is native to the client.
2. **Colour always paired with a word.** Mood, availability, and acquisition state each carry a text label alongside their colour. This is both the aesthetic and, conveniently, the accessible implementation.
3. **Numbers live above the thing they change.** The gain bubble floats over the stat, so cause and effect are visually bound.
4. **One saturated tile marks the active destination.** The nav rail's active state is a filled block, not an underline.
5. **Grouping by phase, collapsible.** The Log groups turns under a named phase bar, which is exactly the shape a 24-turn run needs on screen.
6. **Circular buttons for the five disciplines, rectangles for everything else.** Shape carries meaning: round = pick one of the five.

---

### 6. Web cross-reference

Measured from the official properties on 2026-09-27 through a real browser, because both sites are JS-rendered SPAs and a plain fetch returns an empty shell. Full data with per-element values: this file's `## WEB-FINDINGS.md` section (390 lines, folded from `_scratch/WEB-FINDINGS.md` on 2026-10-02) and seven 1440x900 captures in `_scratch/web/` (`en-home-hero`, `en-home-news-section`, `en-home-characters-bento`, `en-news-listing`, `en-characters-grid`, `jp-home-hero`, `jp-home-news-and-contents`). The study was run twice independently and returned the same core measurements, which is the strongest evidence in this document for the Roboto and no-gradient findings.

**The headline result is that the web properties and the game client are two different design languages, and the differences are systematic, not incidental.**

| Axis | Client (sections 3-5 above) | Official web |
|---|---|---|
| Primary face | rounded humanist (shape-measured) | **Roboto**, variable 100-900. `roboto-condensed` only on character labels. JP adds `YakuHanJP` / `Zen Kaku Gothic New` and a private display family, `midasi-w`, which is literally Roboto files under another name |
| Body ink | warm brown `#6A5641` | neutral grey `#4d4d4d`, on both sites |
| Green | `#7FCC09` (hue ~87) | `#b5d913` game-category green (hue ~74), plus a second `#3ca732` on EN and `#69c832` on JP |
| Gradient | used constantly: arrow-caps, chip fills, button gloss | **zero** `linear-gradient` or `radial-gradient` in any sampled rule. Every background is flat or a bitmap/SVG |
| Shadow | soft cool-cast `box-shadow` | **zero** `box-shadow`; all depth is `filter: drop-shadow()` |
| Blur | none | none. `backdrop-filter` measured `none` on every element sampled; the only instance site-wide is the cookie banner |
| Radius | 5/8/10/14/pill | three values: `0` on most chrome, `8px` news card, `999px` pills |

#### 6.1 What the web study corroborates

Four devices I had derived from the client turn out to be shared brand grammar, which raises confidence in them materially:

1. **`clip-path` notches and angled edges.** `.playnow a` uses `polygon(13px 0, 0 100%, 100% 100%, 100% 0)`; `.playnow-label` and `dd.update` use ribbon-notch polygons. This is the same silhouette family as the client's arrow-cap choice button and notched Back button. The arrow-cap in DESIGN.md §6.1 is now supported by two independent sources.
2. **`skew(±15deg)` tabs with counter-skewed text.** Present on both sites. Corroborates the parallelogram discipline banner and event tag.
3. **Sparkle texture painted onto buttons via `::before`/`::after` SVG** (`decoration_base.svg`, `decoration_square.svg`). Corroborates the button sheen rule and the sparkle motif.
4. **Double-chevron affordance** and the `teitetsu` bullet emblem recur on both. Corroborates §3.6.

The absence of glass and of blur is confirmed on both surfaces, so the "no glassmorphism" rule is now backed by the client *and* the web.

#### 6.2 What the web study contradicts, and the ruling

**The font.** Cygames' web is Roboto. The client is rounded. DESIGN.md §4.1 mandates rounded and bans Roboto, so the web evidence runs against the primary type decision.

The ruling stands, deliberately: the tool's job is to feel like the software a Trainer already spends hours inside, which is the client. The web property is a marketing site with different constraints (photography, brand campaign art, a fixed header over full-bleed imagery). Adopting Roboto because Cygames does would be copying the wrong artifact. The counter-evidence is recorded in DESIGN.md §4.1 and flagged as an owner decision rather than hidden.

Two things *do* transfer from the web's typography and are adopted:
- **Weight and slant as the hierarchy device.** On the Global site the rule is `700 + italic = label`, `400 + roman = content`, with letter-spacing at 0.02/0.05/0.08em tiers and `text-transform` never used. That is a disciplined system, and it is exactly the mechanism behind the celebratory italic register in DESIGN.md §4.3.
- **`text-transform` is not used on the EN site at all.** Case is authored. Consistent with §4.2's rule.

**The brown ink.** `#4d4d4d` on both web properties, not brown. Same ruling: the client's brown is measured at 18.9% coverage of UI frames and is what makes its light UI feel friendly rather than clinical. Kept.

**The second green.** `#b5d913` is 13 degrees yellower and considerably lighter than the client's `#7FCC09`. Not adopted; the client value is measured from the surface we are imitating. Worth knowing that a Trainer who has only seen the website will find `#b5d913` more familiar than `#7FCC09`, and that this is the kind of detail that decides whether a theme reads as "this game" or "a fan site about this game".

#### 6.3 One pattern the web has that the client does not, and we should steal

**Category colour doing triple duty.** On `umamusume.com` a single `--color-category` per record drives the chip fill, the timestamp ink, and the colour of the "Details" affordance. On the JP site it is set inline per card. It is how both sites get identity in the content area without logos.

That maps cleanly onto this tool: a run's `status`, a skill's `type`, or a source's `source_key` could each own one accent that colours its chip, its timestamp, and its open-affordance together. It is cheaper than a per-record illustration and more coherent than colouring each element independently. Recorded as a candidate for the run list and the review queue; not yet in DESIGN.md, because it needs a contrast pass per theme first (CONSTRAINTS D-103).

#### 6.4 Layout note

Neither web property is a desktop-fixed canvas: EN is `vw`-derived off 1440 with three width tiers breaking at 900 and 2000, JP sizes in multiples of 3.75px off a 15px root. Both are responsive. This does not change PRD §2's desktop-only posture, but it does mean the two-region frame in DESIGN.md §8.1 stacking below 1024px is consistent with how the franchise behaves on a narrow window.

#### 6.5 Limits of this study

- The first study pass failed to capture `/characters/` on a tool timeout; the second pass captured it. `roboto-condensed` is registered but *unloaded* on `/` and `/news/` and loads only on `/characters/`, so the condensed finding is scoped to character name and VA labels, not to the site generally.
- The header blue on EN is a bitmap (`bg_blue.Ctkr-Cw2.jpg`) behind a transparent bar, so its exact hex was not sampled. The declared token is `#2a5dfa`.
- `prefers-reduced-motion` was not exercised; motion values are read from `transition` declarations, not observed.
- Fonts were identified from `@font-face` registration and computed `font-family`, which is reliable for family but says nothing about whether Cygames licensed a modified cut. `midasi-w` being Roboto under a private name is evidence they do repackage, so "Roboto" here means "the files they serve", not necessarily "the Google font".


---

### 7. What transfers, and what does not

#### 7.1 Transfers directly
- Light, near-white surface with brown ink (§3.2). Decisive.
- Action green at hue ~87°, used for affirmative actions only.
- Orange-up / blue-down delta semantics.
- Preview-before-commit as the interaction spine.
- Two-window desktop layout; persistent right rail.
- Card-shaped turn indicator rather than a progress bar.
- Six-column stat band with grade badge, value, and cap.
- Collapsible phase grouping for the turn timeline.
- Ribbon/capsule headers with lattice bleed; arrow-cap and notched silhouettes; scalloped gain bubbles.

#### 7.2 Does not transfer, and why
- **3D rendered icons.** We cannot ship Cygames' art. The substitute must be flat glyphs drawn in the same *weight and softness* family, not a hairline icon set.
- **Per-trainee accent tinting.** Measured directly: Oguri Cap's HUD is green (`194819`, `202142`), Mejiro McQueen's is blue (`230755`). The client tints the HUD to the character. Our schema has no colour on `umamusume` and adding one is a schema change with no PRD requirement behind it, so the app keeps **one fixed accent** and reserves per-stat colour for the five disciplines. Flagged as an open question in DESIGN.md.
- **Photographic backgrounds and character portraits.** PRD §6.13 rules out trainee image uploads. The scene layer becomes a flat faceted field.
- **Celebratory italic display type** at race-result scale. Our tool has no race results (PRD §6.11); using it would be costume, not design. Reserved for the run-completion moment only.
- **Glassmorphism, dark chrome, neon trim.** Not present in the source material at all.

#### 7.3 Where the game UI must be *simplified*, not copied
The client is built for a controller/touch rhythm with a character model carrying the emotional load. A logging tool is read-heavy and keyboard-driven. Three places the copy-the-game instinct would hurt us:
- The client's centred capsule headers waste horizontal space at desktop widths; ours keeps the shape but left-aligns.
- Circular discipline buttons are 90 px of chrome to say "Speed"; ours keeps the round glyph as the identifier but pairs it with a text label and a keyboard hint.
- The client's stat band shows one value at a time. Our run view needs the whole turn series, so the band becomes a header and the timeline becomes the body.

---

### 8. Unverified, stated plainly

- The exact font families used by the Global client. Screenshots give shape, not identity. Section 6 covers the *web* properties only.
- The precise hex of the grade-badge letters (S/A/B/C/D/E/F/G). The badges are ~28 px in the source frames and my block averages landed on the white interior rather than the letter fill. Values in §3.2 for grade colour are cluster peaks, which is weaker evidence than the point probes.
- Whether the JP and Global clients differ in UI colour at all, beyond the language. The corpus is entirely one client and I cannot tell which from the frames.
- Whether the discipline-banner colour is per-trainee, per-scenario, or per-season. Two data points (Oguri Cap green, Mejiro McQueen blue) are consistent with per-trainee but do not prove it; a third character would settle it and the corpus has more frames I did not open.
- The mood-pill colour for `NORMAL`, `BAD` and `AWFUL`. The two upper tiers were captured by point probe (§4.3); the other three are derived and marked provisional. The five tier *strings* are no longer in this list — the client's own Mood Effect panel supplies them, recorded in §4.3.


---

## SCREENSHOT-MANIFEST.md

SCREENSHOT-MANIFEST


Triage of `docs/game-screenshots/` for scenario and screen-type coverage. Generated 2026-09-27.
Method and per-screen visual detail live in `RAW-FINDINGS.md`; this file is the coverage index.

### How this was produced

Three passes, none of which reads all 1,160 frames:

1. **Signature pass** (`_scratch/triage.py`) — dimensions, mean colour, luminance and a 4x4 cell grid for every frame. Output `signatures.json`.
2. **Perceptual clustering** (`_scratch/cluster.py`) — dHash, Hamming threshold 6, 60-frame rolling window. Collapses 1,160 frames to **751 distinct screens**. Output `clusters.json`.
3. **Scenario-chip montage** (`_scratch/scenario_probe.py`) — crops the turn-chip region from 96 frames sampled at a fixed stride across the whole capture span and tiles them into one sheet (`_scratch/scenario_chips.png`). This is what actually answers the scenario question, and it is why an earlier claim in this package had to be withdrawn.

### Corpus shape

| Measure | Value |
|---|---|
| Total frames | 1,160 |
| Distinct after perceptual dedupe | 751 |
| Capture span | 2026-07-14 to 2026-07-18, four sessions |
| Full desktop 1920x1080 | 173 frames, 44 distinct |
| Wide region snips (1200-1570 px) | 96 distinct |
| Mid snips (780-1200 px) | 117 distinct |
| Portrait snips (< 780 px) | 538 distinct |
| Largest single cluster | 58 frames (`2026-07-18 001811.png`) |
| Median frame luminance | 193 / 255 |

Filenames carry no semantic information (`Screenshot YYYY-MM-DD HHMMSS.png`, Windows Snipping Tool), so category assignment is content-derived. Full desktop frames are the highest-value stratum because they alone show panel relationships and chrome together.

### Scenario coverage

This is the section that matters for the comparative request, and the honest answer is narrower than the question.

| Scenario | Evidence in corpus | Coverage |
|---|---|---|
| **Unity Cup** | Turn chips reading `Until the Unity Cup` / `Unity Cup Begins at End of Turn` across the 96-frame montage; `TEAM RANK B 11/16` and `Result Pts` counters; Team Showdown opponent select | **Dominant.** Deep coverage of training HUD, choices, log, calendar, team screens |
| **Ura Finale** | `012428` carries a `Place 1st in URA Finals` goal with a `FINISHED` state; `014154` shows the same goal line with `Power Lvl 5` | **Minimal — two frames, not a coverage basis.** Both are known only for their goal line; nothing else about URA's surfaces is recorded in this corpus, and `014154`'s `Power Lvl 5` implies a training screen that has not been described or cropped here. "Present" means the label is legible in the corpus, not that URA's UI is represented — designing URA-specific layout from this would be designing from two goal-line crops. Reading those two frames in full is the cheap way to widen this row |
| **Trackblazer / Climax** | None found | **Absent** |
| **Our Grand Concert** | None found | **Absent** |

**A three-way scenario comparison cannot be performed on this corpus.** Two of the four Global scenarios have no frames at all. A `SCREENSHOT-MANIFEST` that listed URA, Unity Cup and Trackblazer clusters with comparable counts would be reporting structure that is not there.

**Correction on the record.** An earlier revision of `RAW-FINDINGS.md` and `DESIGN.md` asserted that the entire corpus was one scenario. That was an overclaim from roughly ten inspected frames and is withdrawn: Ura Finale material exists. The claim was replaced by the table above, which is derived from a 96-frame stratified sample.

### Screen-type coverage

| Screen type | Representative frame | Scenario | Notes |
|---|---|---|---|
| Training HUD, full | `2026-07-14 194819`, `2026-07-14 202142` | Unity Cup | Turn chip, date strip, goal row, Energy gauge, mood pill, discipline banner with activity name, six-column stat band, gain bubbles, Failure badge, five circular discipline buttons, support card rail |
| Training HUD, blue theme | `2026-07-17 230755` | Unity Cup | Same layout, different accent; evidence the HUD tints per trainee |
| Event choice panel | `2026-07-14 124128`, `124926` | Unity Cup | Tag ribbon, title ribbon, arrow-cap choice banners, outcome preview with `Max Energy +4` cap language |
| Log / turn history | `2026-07-17 234521` | Unity Cup | Avatar overhang, sign-coloured prose, collapsible phase bar |
| Skill acquisition | `2026-07-17 234521` | Unity Cup | Hint discount badge, cost stepper, gold selected row |
| Umamusume Details modal | `2026-07-17 232345` | Unity Cup | Stat band, aptitude table, **Growth Rate row**, tab control, unique-skill gradient chip |
| Race calendar | `2026-07-17 230755`, `235229` | Unity Cup | 24-cell half-month grid, `Goal` pennant, `Scheduled` pill, three cell states |
| Team Showdown | `2026-07-17 235229` | Unity Cup | Rank laurels, team colour bands |
| Race result / live | `2026-07-18 000919`, `235511` | Unity Cup | The only dark surface in the client; amber dot-matrix on charcoal |
| Photo Album | `2026-07-18 134106` | Unity Cup | Notched Back button, selected-tile glow |
| NPC coaching bubble | `025519`, `038100`, `038751`, `058814`, `194319` | Unity Cup | Green-clad staff avatar with white bubble; the advisory pattern |
| Inheritance select | `121214`, `122453` | Unity Cup | Trainer Select / Legacy Select, `Affinity` |

### Facility activity names observed

Extracted from the discipline banner across the montage. **Scope of the claim, corrected 2026-09-27:** every frame that shows one of these names is a **Unity Cup** frame (see the coverage table, which records zero Trackblazer and zero Our Grand Concert training HUDs). So the list evidences that activity names vary **per facility within Unity Cup**, and that the banner carries them. It does **not** evidence that facility layouts differ *between* scenarios — that comparison is not available on this corpus, and this file's own methodology line says so. Any artifact that cited these six names as cross-scenario evidence was over-reading a single scenario's data; the claim is corrected in `CONSTRAINTS.md` D-187 and `DESIGN.md` §6.4b and §6.22, and the rule D-187 states (a banner without its activity line is incomplete) is unaffected.

`Breaststroke`, `Freestyle`, `Long-Distance Swimming` (Stamina, pool), `Incline` (Guts), `Running` (Speed), `Dirt` (Power) — all Unity Cup.

### Scenario guides fetched from the web, same date

| Scenario | Guide | Retrieved | Effect on the design |
|---|---|---|---|
| Ura Finale | `game8.co/.../archives/536520` | **Yes, in full** | Caps are `1200 base + 200`; gains past 1200 halve; camp recurs in Classic **and** Senior; scenario NPCs and a Scenario Link Character; scenario reworked 2026-07-01. Drives `CONSTRAINTS.md` D-211 to D-218 |
| Unity Cup | `archives/545572` | **No**, redirected off the scenario page | Nothing. Unity Cup claims rest on the screenshot corpus only |
| Trackblazer | `archives/580723` | **No**, redirected to a JP training-effects page | Nothing. Still no Trackblazer evidence of any kind |

The scenario-comparison gap identified above is therefore **unchanged**: one of three guides was retrieved, and the corpus has no Trackblazer frames at all. A three-way comparison remains impossible.

### Not present in the corpus

Scouts / gacha screens, support-card composition, settings, Trackblazer chrome, Our Grand Concert chrome, and any non-Unity-Cup training loop. Where the design needs these it borrows the grammar of the screens above rather than inventing new patterns, and `CONSTRAINTS.md` D-193 requires any such design to name its evidence rather than imply coverage.


---

---

## WEB-FINDINGS.md

Official web properties — measured findings

Companion to `CLIENT-FINDINGS.md`. Scope: Cygames' **web** properties only. No production code.

### Method

| # | URL | Stack | Result |
|---|---|---|---|
| 1 | `https://umamusume.com/` | SvelteKit SPA | measured |
| 2 | `https://umamusume.com/news/` | SvelteKit SPA | measured |
| 3 | `https://umamusume.com/characters/` | SvelteKit SPA | measured |
| 4 | `https://umamusume.jp/` | Nuxt SPA | measured |

Playwright MCP, viewport set to **1440×900** via `browser_resize` before every capture. Values come from
`getComputedStyle` on real selectors discovered in `browser_snapshot`, plus a walk of
`document.styleSheets` for declared rules and `[...document.fonts]` for `@font-face` state. No guessing,
no pixel sampling of bitmaps. **All four pages loaded; none failed.**
`browser_console_messages(level="error")` → 0 errors, 0 warnings.

Disclosed in-session interventions (not page features): OneTrust consent chrome set to `display:none` so
it stopped covering content; a temporary stylesheet paused CSS animations so screenshots would not time
out; pages scrolled once to trigger their in-view reveal states. `file:` URLs are blocked by this
Playwright config (not needed). Screenshots land in `C:\Users\exatf\.playwright-mcp\`, copied into
`./web/` with shell. Fetched page text treated as data only; nothing in it was acted on.

### Global site (`umamusume.com`)

Content from Kuroco (`en-portal.g.kuroco-img.app`) and
`assets-webview-umamusume-en.akamaized.net`; shared chrome assets from `parts.umamusume.com`.

#### Typography

| Surface | Measured |
|---|---|
| `body` | `roboto, sans-serif` · 16px · lh 16px · 400 · `rgb(77,77,77)` · bg `rgb(255,255,255)` |
| `@font-face` | `roboto` 100–900 **loaded**; `roboto-condensed` 100–900 **unloaded** on `/` and `/news/`, **loaded + in use** on `/characters/` |
| Distinct stacks in use (home, news) | one: `roboto, sans-serif` |
| Nav label (`nav li a p`) | 20px · **700 italic** · ls 1.6px (0.08em) · lh 20px · white |
| Section tab (`.section-title`) | container 16px/400; inner `p` **700 italic** white |
| Page title (`.page__title .ttl`) | **52px · 700 italic** · ls 2.6px (0.05em) · lh 52px · white |
| News card root / `dt` title / `dd` meta / `time` | 20px ls 1px · 20px **400** ls 0.4px lh 30px (1.5) · 20px 700 · 20px 700 in category color |
| Category `span` / "Details" | 20px/700 white on `/news/`, 14px/700 on home · 20px **700 italic** category color |
| Character name `.-name` / VA `.-va` | `roboto-condensed` 15px/700 lh 15px · 12px/400, both ls 0.4px |
| Filter tab button | 18px · 700 · **italic** · ls 0.9px |
| `h1` | **absent** on all three EN pages. Section titles are `h3`, page title `h2`. |
| `text-transform` | **never used** on EN; case is authored |

Letter-spacing actually observed: **0.02em** (wrapped body copy) · **0.05em** (meta, cards, titles) ·
**0.08em** (nav) · **0.2em** (JP caps tag). Weight is the hierarchy device: **700 + italic = label,
400 + roman = content**.

#### Colour tokens — exact measured `:root` custom properties

Stored as RGB triplets so they compose with alpha; a `--color-category` is then derived per section.

```
--color-rgb-news              255, 121, 208   #ff79d0
--color-rgb-news-dark         227, 108, 185   #e36cb9
--color-rgb-news-sub          255, 239, 247   #ffeff7
--color-rbg-news-game         181, 217,  19   #b5d913   <-- "rbg" is a typo in their token name
--color-rbg-news-media        255, 145,  28   #ff911c   <-- same typo
--color-rgb-characters        140, 131, 255   #8c83ff
--color-rgb-characters-dark   125, 117, 227   #7d75e3
--color-rgb-characters-light  241, 239, 255   #f1efff
--color-rgb-media              76, 174, 255   #4caeff
--color-rgb-media-dark         68, 155, 227   #449be3
--color-rgb-green              60, 167,  50   #3ca732
--color-rgb-blue               42,  93, 250   #2a5dfa
--color-rgb-pink              255, 110, 203   #ff6ecb
--parts-color-pink  #ff6ecb   --parts-color-rgb-blue #2a5dfa   --parts-color-text-base #4d4d4d
--color-text-base #4d4d4d   --color-text-gray #141414   --color-gray #d2d2d2
--color-gray-2 #898989   --color-error #ff0000   --vh 9px (mobile vh JS fallback)
```

Derived / scoped, not on `:root`: `--color-category` (per section: `.top-news` → news, `.top-characters`
→ characters, `.top-media` → media; per row: `.news-item.-game` → `rgb(var(--color-rbg-news-game))`) ·
`--color-modal` (`.modal.-gameplay-movie` → green, `.-about-movie` → blue) · `--viewmore-color` =
`var(--color-category)` · `--height` (header `3.4722222222vw` = 49.78px @1440, measured 50px) ·
`--padding-right` (header nav `1.3888888889vw` / `27.7777777778px` / `0`) · `--ribon` (declared only on
`.article-box h2`: `4vw` / `1.0416667vw` / `20.8333333px` / `2.6666667vw`) · `--xpos/--ypos/--blur`
(shadow components, per width tier).

Section tab fills measured on `/`: News `rgb(255,121,208)` · About `rgb(255,255,255)` ·
Gameplay `rgb(60,167,50)` · Characters `rgb(140,131,255)` · Media `rgb(76,174,255)`.
Character cards on `/characters/` carry **93 distinct accent backgrounds**, one per record — e.g.
`rgb(255,222,249)`, `rgb(255,206,72)`, `rgb(255,205,0)`, `rgb(51,184,57)`, `rgb(233,218,54)`,
`rgb(61,209,215)`. Data, not tokens.

#### Chrome / material

- **`backdrop-filter: none` on every element sampled. No `box-shadow` anywhere.** All depth is
  `filter: drop-shadow()`: news card `0 2px 6px rgb(0,0,0,.2)` · section box & page title
  `3px 3px 10px rgb(0,0,0,.25)` · character thumb `0 0 5px rgb(0,0,0,.3)` · filter tab
  `var(--xpos) var(--ypos) var(--blur) rgba(0,0,0,.25)` → `0 2.78px 6.94px` @1440 · "View more"
  `4.17px 4.17px 13.89px`.
- **Radius = three values**: `0px` (all chrome, tabs, nav, character cards) · `8px` (news card) ·
  `999px` (category pill, filter tabs, sort select, Search button, View more); `50%` on carousel arrows.
  **Borders `0px` on nearly everything** — exception: filter-tab buttons have `border-width: 2.08333px`
  (declared `0.104167vw`) in `--color-gray-2`, switching to `--color-rgb-news-dark` when selected.
- Header: `position: fixed`, measured **50px** tall, `z-index: 999`, `background-color: rgba(0,0,0,0)`.
  The blue is not CSS: an absolutely-positioned `.header__bg` at `z-index:-1` holds
  `<picture><source media="(min-width: 899.98px)" srcset="…/bg_blue.Ctkr-Cw2.jpg">`, `object-fit: cover`.
  Exact hex of that blue is **unknown** (bitmap, not sampled); `--parts-color-rgb-blue: #2a5dfa` is the
  declared token.
- Angled edges via `clip-path`, no pseudo-elements: `.playnow a` →
  `polygon(13px 0, 0 100%, 100% 100%, 100% 0)` on `var(--parts-color-pink)`; `.playnow-label` →
  `polygon(0 0, 0 100%, 100% 100%, calc(100% - var(--ribon)) 50%, 100% 0)`; `dd.update` →
  `polygon(0 0, var(--ribon) 50%, 0 100%, 100% 100%, calc(100% - var(--ribon)) 50%, 100% 0)`.
- **No `linear-gradient` or `radial-gradient`** in any rule matched against
  `news|section|header|card|title|btn|button|top-|chara`. Every `background-image` sampled was `none`
  or a `url()` bitmap/SVG. Gradient sweeps are not part of this system.

#### Layout

- `documentElement.scrollWidth` **1440**, `clientWidth` **1425** (15px classic scrollbar) — no horizontal
  overflow, so responsive at this width. `matchMedia`: `min-width:1200px` and `min-width:1024px` true,
  `max-width:768px` false, `prefers-reduced-motion` false. Real breakpoints from `cssRules`:
  `(max-width: 899.98px)` · `(min-width: 900px) and (max-width: 1999.98px)` · `(min-width: 2000px)` ·
  `(hover: hover) and (pointer: fine) and (min-width: 900px)`. **Three width tiers.**
- **Sizing is vw-derived, not rem.** @1440, 1vw = 14.4px: tab font `1.25vw`=18px, tab
  `19.4444vw × 2.77778vw` = 280×40px, header `--height` `3.4722vw`=49.78px.
- Columns: `.top-section-box` (home news) **860px** centered · `.news-list` **790px**, `display:grid`,
  single column, `gap:15px` on home / **`30px`** on `/news/` (with `margin: 60px 325px 55px`) ·
  `.page-characters` / `.characters-list` **1200px**, `grid-template-columns: 156px ×7`,
  `gap: 45px 18px` · footer 552px, `max-width: 2000px`. Home section heights 1047/690/900/900/517/516px.
  Character card 154×243 (image 148×194, info `padding:5px`). Filter tabs `1fr 1fr 1fr`, 3 × 280×40,
  `gap: 20px`, centered.

#### Motifs (what recurs)

1. **`skew(±15°)` parallelogram tabs.** Container `matrix(1,0,-0.267949,1,0,0)` = `skewX(-15deg)`, inner
   text counter-skewed `+15deg` so type stays upright. Every section tab and page title. Solid category
   fill, white 700-italic label, zero radius.
2. **Ribbon / chevron notch** cut into button and tag edges with `clip-path: polygon()` driven by
   `--ribon`. Reads as a banner end, not a rounded badge.
3. **Sparkle texture on interactive surfaces.** Filter tabs and "View more" layer `decoration_base.svg` /
   `decoration_square.svg` on `::before` **and** `::after` at 100%×100%, `::after` mirrored
   (`scaleX(-1)`), opacities `.4`/`.8`, and on selected `filter: brightness(0) invert(1)`. A tiled asset,
   not a CSS effect. (Glyph shape inside the SVG unverified — see Unknowns.)
4. **Double chevron** as the universal "open" affordance: `arrow_double.svg`, or inline
   `viewBox="0 0 38 42"` with two chevron paths where the **first is `opacity="0.5"`**.
5. **Nav bullet:** `teitetsu.svg`, 11×11.7px, `margin-right: 8px`, rotated **145°**
   (`matrix(-0.819152, 0.573576, -0.573576, -0.819152, 0, 0)`), before every nav label.
6. **Finish-line bar:** `/characters/` section heading is inline SVG with a `246×10` rect in `#E2E0FF`
   whose right end is a separate path notched into a chevron.
7. **Fixed full-bleed pattern layer:** `img.pattern-bg`, `position: fixed`, `z-index: -10`,
   `max-width: 2000px`, `object-fit: cover`, `object-position: center top`, source `bg.B9VZMSei.jpeg` — a
   pale lavender faceted field. Sections are transparent over it; section boundaries are hard **diagonal
   cuts** between full-bleed art panels.
8. **No star/sparkle SVG *elements* in the DOM.** `/` has 21 `<svg>`, all named `arrow` / `close` /
   `gameplay_headline_*` / `gameplay_frame` / `kv_txt_*` / `arrow_reverse`. Racetrack and venue imagery
   exists only **inside bitmaps** (KV, banners, `top-*__bg`), never as CSS.
9. **Category color triples up:** pill fill, `<time>` ink and the "Details" affordance all read the same
   `--color-category`. One accent per record, three uses.

#### Motion (from `transition` values)

- Reveal wipe: `clip-path: inset(0 100% -20% 0)` → `inset(0)` under `.loaded .is-show`,
  `clip-path .4s cubic-bezier(.165,.84,.44,1) .3s`. Panel entrance: `translateY(80px)` + `opacity:0` →
  identity, `transform .7s cubic-bezier(.19,1,.22,1), opacity .7s cubic-bezier(.165,.84,.44,1)`.
- Character grid stagger: same clip wipe, `cubic-bezier(.25,.46,.45,.94)`, delays `.15 .20 .25 … .85s`
  (**50ms per index**, 15 slots).
- Card hover: `transform .3s cubic-bezier(.175,.885,.32,1.275)` (back-out overshoot). Tab/button hover:
  `transform: scale(.95)`, `.5s cubic-bezier(.19,1,.22,1)` — **press-in, no color flip**. Play Now color
  swap: `background-color .3s, color .3s cubic-bezier(.19,1,.22,1)`.
- Four curves total, dominated by `cubic-bezier(.19,1,.22,1)`.

#### News list — dates and tags as actually built

```html
<li class="news-item -type-news -game">
  <a class="news-card" href="/news/1076">
    <img class="news-card__image" src="…/Thumbnail/banner_30100037_L1790287200.png?c=…">
    <dl><dd><span>Game</span> <time>2026-09-24&nbsp;22:00&nbsp;(UTC)</time></dd>
        <dt>The story event Illuminate the Heart is coming soon!</dt></dl>
    <div class="detail-text"><p>Details</p><svg viewBox="0 0 38 42" class="-arrow">…</svg></div>
  </a>
</li>
```

- Date: **`YYYY-MM-DD HH:MM (UTC)`** — hyphen date, space, 24h time, literal `(UTC)` suffix, joined with
  `&nbsp;` so the three parts never wrap apart. Every sampled item ends `22:00 (UTC)`.
- Category is a **word** (`Game` / `Media`), mixed case, in a solid pill; the `<li>` class (`.-game`,
  `.-media`) sets `--color-category`, consumed by pill, date and "Details". Filter set is `All News` /
  `Game` / `Media` — three equal pills, one selected.
- Titles sentence case, weight **400**, no bold, **no divider rules**; separation is `gap: 30px` between
  white cards over the fixed pattern layer. **10 cards render**, then one "View more" pill — no page
  numbers, no date-group headers, no month rails.
- `.dd.update` notched-ribbon rule exists in EN CSS but **no instance** in the sampled 10.

### JP site (`umamusume.jp`)

Nuxt (`/_nuxt/…`, `data-v-*` scoped attributes), content from microCMS
(`images.microcms-assets.io`) + `prd-info-umamusume.akamaized.net`.

#### Typography

- `body`: `YakuHanJP, Roboto, "Zen Kaku Gothic New", sans-serif` · **15px** · lh 15px ·
  **inherited weight 500** · `rgb(77,77,77)`.
- `@font-face`: `YakuHanJP` (100–900 declared; **400, 500 loaded**) · `Noto Serif JP` (many faces
  declared, **all unloaded**) · `Raleway` (**400/500/700 loaded**) · `Roboto` (**400/500/700 loaded**) ·
  `Zen Kaku Gothic New` (**400/500/700 loaded**) · **`midasi-w`** (custom display face, **400 loaded**).
  Stacks in use: body (1229 elements) · `midasi-w, Raleway, sans-serif` (125) · `Times New Roman` (1).
- Section tab `.anim-ttl`: 15px · **500** · white; container `skewX(-15°)` with
  `clip-path: inset(0 100% -20% 0)` reveal, `clip-path .4s cubic-bezier(.165,.84,.44,1) .3s`, height
  **46.5px**; inner `.ttl` counter-skewed `+15°`. Sub-labels use a **lighter skew**:
  `matrix(1,0,-0.176327,1,0,0)` = `skewX(-10deg)`.
- **Nav labels are SVG wordmarks**: `background-image: url(/_nuxt/images/nav_about-*.svg)` with
  `font-size: 0`, `text-transform: capitalize`, `midasi-w` only as fallback. 19.5px/500 white, item
  height 48.75px; sub-items 18px / 37px. **`capitalize` is used on JP labels; EN never uses
  text-transform.**
- News card: `dt` 15px/500 lh **22.5px** (1.5) · `dd` 15px flex · `time` 15px/500 category color ·
  category `label` `midasi-w` 15px/500 **ls 3px (0.2em)** white, fixed **113×18**, `radius 28.5px`,
  `margin-right 18.75px` · `detail-text` 15px category color.
- `dd.update` ("[追記]" amended marker): 13.5px/500 white on `#4d4d4d`, `padding: 0 37.5px`,
  `clip-path: polygon(0 0, 7.5px 50%, 0 100%, 100% 100%, calc(100% - 7.5px) 50%, 100% 0)` — **both ends
  notched**.

#### Colour tokens — exact measured `:root`

```
--color-blue #2a5dfa   --color-green #69c832   --color-yellow #ffba00   --color-orange #ff9600
--color-gray #d2d2d2   --color-text-base #4d4d4d   --color-text-gray #141414   --color-error red
--scroll-bar-width 15px   --vh 9px
```

**8 colour tokens vs EN's 22.** No `--color-rgb-*` triplet layer. Category color is injected **inline
per record**: `<a class="news-card" style="--color-category: #b5d913">`.

Section tab fills measured: News `rgb(255,121,208)` · About `rgb(60,176,50)` · Contents
`rgb(76,174,255)` · Character `rgb(140,131,255)` · Special `rgb(255,113,47)` · Goods `rgb(255,200,51)`.
Game category `rgb(181,217,19)`.

#### Chrome / material

- Card: bg `#fff`, **`border-radius: 9px`**, `padding: 11.25px 11.25px 7.5px`,
  `filter: drop-shadow(rgba(0,0,0,.2) 0 2.25px 3.75px)` — tighter, less spread than EN.
  `.btn-movie` / `.btn-viewmore`: `border-radius: 3px`. Carousel arrows: 64×64, `radius 50%`, transparent.
- `backdrop-filter: none` everywhere sampled; `box-shadow: none`; `border: 0px`.
- `.new-icon` badge: 44×45 `<img src="/_nuxt/images/icon_news-*.png">` at the card's top-left.
- `body`/`html` bg `rgb(255,255,255)` with **no background image** — unlike EN's fixed pattern layer.
- **JP spacing is a 3.75px grid** (15px ÷ 4): 3.75 · 7.5 · 11.25 · 18.75 · 22.5 · 37.5 · 46.5 · 94.5.
  EN uses vw fractions of 1440. Same visual result, different unit philosophy.

#### Layout

- `scrollWidth` 1440, `clientWidth` 1425, no overflow, `scrollHeight` 3350 on `/`. `.index__width`
  container **1251px**, `margin: 90px 94.5px 0`, `max-width: none`.
- Home is a **two-column asymmetric composition**, not full-width bands: `top-news` 576×667 @ x=87,y=999 ·
  `top-about` 605×305 @ x=734,y=999 · `top-contents` 605×305 @ x=734,y=1360 · `top-character` 1251×560 @
  y=1755 · `top-special` 473×134 @ x=87,y=2405 · `top-goods` 473×134 @ x=642,y=2405. Footer 661px.
- News panel is a **pink rounded container** holding 5 rows with a centered pink "View more" pill.
  Header 49px, transparent, `translateY(-48.75px)` when hidden on scroll.
- Body text 1479 chars on `/` — the page is almost entirely bitmaps (74 `<img>`).

#### Motifs

Same parallelogram tabs, ribbon `clip-path` notch and `teitetsu` bullet emblem
(`class="nuxt-icon--fill teitetsu"`, 19.5px). **JP double-chevron differs:** inline
`viewBox="0 0 31.78 48.19"`, two *identical* chevron paths side by side, rather than EN's second-at-50%.
**JP-specific:** section tabs are **two-part** — a Latin word plus a smaller, darker parallelogram with
the Japanese gloss (`News` + `新着情報`, `About` + `ウマ娘について`, `Contents` + `コンテンツ`,
`Character` + `キャラクター`). Sparkle-texture pseudo-element overlays were **not observed** on JP home
buttons; JP uses flat fills.

### Divergences (EN vs JP)

| Axis | EN `umamusume.com` | JP `umamusume.jp` |
|---|---|---|
| Build / CMS | SvelteKit + Kuroco | Nuxt + microCMS |
| Root font-size | 16px | 15px |
| Body weight | 400 | 500 (inherited) |
| Families in use | 1 (Roboto); Condensed only on character labels | 3 (YakuHanJP/Zen Kaku body, `midasi-w`/Raleway display, Roboto) |
| Unused declared | — | `Noto Serif JP`, all faces unloaded |
| Colour tokens | 22 on `:root`, incl. `--color-rgb-*` triplets | 8 on `:root`, no triplet layer |
| Category color | class-driven `.-game` → `--color-category` | **inline `style="--color-category: #b5d913"`** per card |
| Header | fixed 50px, bitmap blue behind, pink angled Play Now tab, hamburger modal <900px | 49px transparent, wordmark left, **SVG-wordmark nav**, hides on scroll |
| Section tab | one skewed parallelogram, Latin only | **two-part** tab, Latin + Japanese gloss |
| `text-transform` | never | `capitalize` on labels |
| Date | `2026-09-24 22:00 (UTC)` — explicit zone | `2026.09.26  12:00` — dots, double `&nbsp;`, **no zone** |
| Category tag | `Game`, 20px/700 Roboto, radius 999px, size follows card | `GAME`, `midasi-w` 15px/500, **tracking 3px**, fixed 113×18, radius 28.5px |
| Amended marker | CSS rule present, no instance seen | `[追記]` dark-gray double-notched ribbon, live |
| Card radius / shadow | 8px · `0 2px 6px /.2` | 9px · `0 2.25px 3.75px /.2` |
| Background | fixed full-bleed pattern `img` at `z-index:-10` | plain white body; art per section |
| Layout | stacked full-width bands, 790px news column | two-column asymmetric, 1251px container |
| Sparkle texture on buttons | yes (`decoration_base` / `decoration_square.svg`) | not observed |
| Unit philosophy | `vw` fractions of 1440 | multiples of 3.75px (15px/4) |

**Shared invariants** (the real brand constant, on both): `#4d4d4d` text · `#d2d2d2` · `#141414` ·
`#2a5dfa` · `#ff79d0` news pink · `#8c83ff` characters purple · `#4caeff` media/contents blue ·
`#b5d913` game green · `skew(±15°)` tabs with counter-skewed text · `clip-path` ribbon notches ·
`drop-shadow`-only depth · **zero** `backdrop-filter` · zero `box-shadow` · category color doubling as
date ink · double-chevron affordance · `teitetsu` bullet emblem · `clip-path: inset()` reveals on
`cubic-bezier(.165,.84,.44,1)` and `cubic-bezier(.19,1,.22,1)`.

### What transfers to an in-app tool — and what does not

#### Transfer (cheap, distinctive, no assets required)

| Pattern | Why it survives the move |
|---|---|
| **Category-as-accent** — one `--color-category` per record driving chip fill + timestamp ink + the "open" affordance color | Maps straight onto run / turn / skill categories; gives identity with no logo in the content area. Prefer the **JP form** (inline `style="--color-category: …"` on the row) over EN's `.-game` class enumeration, because the value comes from the data. |
| **`skew(±15°)` tab with a counter-skewed inner `span`** | A panel label with real character, pure CSS. The counter-skew is mandatory or the text leans. |
| **Ribbon `clip-path` polygon** on status chips | Notched ends read as a banner and survive background changes a rounded chip would not. |
| **Three-value radius scale** `0` / `8px` / `999px` | Matches both sites; nothing else is used. |
| **700 italic = label, 400 roman = content** | The strongest readable pattern in the news list — no dividers, no bold titles, still scannable. |
| **Letter-spacing scale** 0.05em body/labels, 0.08em nav/display, 0.2em all-caps tags | Three values, both sites agree. |
| **Date rendering** `YYYY-MM-DD HH:MM` + literal zone suffix in one string, `&nbsp;`-joined so it never wraps apart | For a local-only tool: date-only stays date-only; anything carrying a time names its zone. |
| **`drop-shadow`-only depth**, no `box-shadow`, no `backdrop-filter` | The measured recipes are subtle; keeps nested surfaces from stacking shadows. |
| **Motion budget**: 4 curves, 300/400/700ms, `clip-path: inset()` wipes, `scale(.95)` press (no color flip), 50ms per-index stagger capped at ~10 | Small, consistent, cheap. |
| **Filter pills as `1fr 1fr 1fr`** — selected = category fill + white; unselected = white + `#898989` text and ~2px `#898989` border. "View more" instead of pagination | Works for a small fixed category set. |
| **`dl` / `dd` / `dt` / `time` semantics** for a dated row | Both sites use it; keep the markup. |

#### Do not transfer

| Reject | Reason |
|---|---|
| Fixed full-bleed photographic background (`img.pattern-bg`, `z-index:-10`) | Dense data needs a plain surface; the lavender faceted field fights tables and charts. |
| Bitmap behind the header bar | Use the `#2a5dfa` token as a solid. |
| Nav labels as SVG wordmarks with `font-size: 0` | Breaks scaling, i18n, search, a11y. |
| `vw`-only sizing | 1vw = 14.4px couples type to viewport width; a desktop tool wants `rem`. |
| `line-height: 1.0` on body/meta copy | Fine for one-line labels, wrong for wrapped titles. Both sites do use 1.5 on the news `dt` — keep that. |
| 52px italic display titles | A tool needs ~20–24px panel headings, not a poster. |
| 93 per-record accent colors as `:root` tokens | Keep them as data on the row. |
| `filter: drop-shadow()` on every card in a long list | A paint composite per node. Restrict to hover/selected. |
| Sparkle-texture pseudo-element overlays | Asset-dependent, fail contrast review on light fills; this repo's `anti-slop` bar rejects decorative texture on controls. |
| OneTrust chrome, store badges, SNS row, carousel machinery, `--vh` mobile hack | Marketing-site scaffolding, not tool UI. |
| `--color-rbg-news-game` / `--color-rbg-news-media` | Misspelled token names ("rbg"). Copy the colors, not the names. |

### Measured values worth turning into tokens

| Token | Value | Source |
|---|---|---|
| `--ink` / `--ink-strong` / `--line` / `--muted` / `--surface` | `#4d4d4d` / `#141414` / `#d2d2d2` / `#898989` / `#ffffff` | both `:root`; `--muted` = EN `--color-gray-2` (unselected tab text/border) |
| `--brand-blue` | `#2a5dfa` | EN `--color-rgb-blue`, JP `--color-blue` |
| `--accent-news` / `-dark` / `-tint` | `#ff79d0` / `#e36cb9` / `#ffeff7` | EN `--color-rgb-news{,-dark,-sub}`, JP News tab; `-dark` is the selected tab border |
| `--accent-pink` | `#ff6ecb` | EN `--parts-color-pink` (Play Now fill) |
| `--accent-characters` / `-dark` / `-tint` | `#8c83ff` / `#7d75e3` / `#f1efff` | EN `--color-rgb-characters{,-dark,-light}`, JP Character tab |
| `--accent-media` / `-dark` | `#4caeff` / `#449be3` | EN `--color-rgb-media{,-dark}`, JP Contents tab |
| `--accent-game` | `#b5d913` | EN `--color-rbg-news-game`, JP inline `--color-category` |
| `--accent-orange` / `--accent-special` / `--accent-goods` | `#ff911c` / `#ff712f` / `#ffc833` | EN `--color-rbg-news-media`; JP Special + Goods tabs |
| `--accent-green` | `#3ca732` | EN `--color-rgb-green` (Gameplay tab, modal) |
| `--danger` | `#ff0000` | EN `--color-error` |
| radius | `0` / `8px` / `999px` | EN chrome / EN news card / EN pills (JP card 9px, JP tag 28.5px) |
| shadow-card / -panel / -raised / -control | `drop-shadow(0 2px 6px rgb(0 0 0/.2))` · `3px 3px 10px /.25` · `0 0 5px /.3` · `0 2.78px 6.94px /.25` | EN `.news-card` · `.top-section-box`+`.page__title` · `.characters-list-thumb` · filter tab @1440 |
| skew | `15deg` (`atan 0.267949`) | both, section/page tabs; JP sub-labels `10deg` (`0.176327`) |
| header height | `50px` (declared `3.4722vw`) | EN |
| tab size / type | `280×40px`, `gap 20px`, 3-up · `18px / 700 / italic / ls 0.9px` | EN `.news-category__tab` @1440 |
| meta / title type | `20px/700/ls 1px` + `20px/400/ls 0.4px/lh 30px` (EN) · `15px/500` + `15px/500/lh 22.5px` (JP) | news `dd` / `dt` |
| label-caps tracking · condensed label | `0.2em` · `15px/700` + `12px/400`, `ls 0.4px` | JP category `label` (3px on 15px) · EN character name/VA |
| list gap · content width | `30px` (EN `/news/`), `18px` col / `45px` row (EN grid) · `790px` / `1200px` / `1251px` | EN news · EN grid · JP |
| reveal / entrance | `clip-path: inset(0 100% -20% 0) → inset(0)`, `.4s cubic-bezier(.165,.84,.44,1) .3s` · `translateY(80px)`+`opacity 0→1`, `.7s cubic-bezier(.19,1,.22,1)` | both · EN |
| press / card hover / stagger | `scale(.95)`, `.5s cubic-bezier(.19,1,.22,1)` · `.3s cubic-bezier(.175,.885,.32,1.275)` · `50ms` per index, capped 15 | EN tabs · EN `.news-card` · EN `.top-characters__list` |
| date format · breakpoints · scrollbar | `YYYY-MM-DD HH:MM (UTC)` / `YYYY.MM.DD␠HH:MM` · `900px`, `2000px` (3 tiers) · `15px` classic | EN / JP · EN `cssRules` · both (JP `--scroll-bar-width`) |

### Unknown / could not determine

- **Header blue, exactly.** It is a bitmap (`bg_blue.Ctkr-Cw2.jpg`), not a CSS color; I did not sample
  pixels. `#2a5dfa` is the declared token, not a measurement of that image.
- **Resolved `--ribon`.** Computed to `0` on the header at 1440 on `/news/`, so the Play Now angle came
  from the literal `13px` in its polygon; declared values were found only on `.article-box h2`. The
  intended notch depth is unknown.
- **Hover / focus-visible / active / disabled states.** Not triggered. Resting computed styles plus the
  `:hover` rules quoted above only; nothing for keyboard focus, and no focus-ring spec found.
- **Dark mode.** No `prefers-color-scheme` query in the stylesheets I could read on either site. Unknown
  whether one exists behind a cross-origin sheet (those threw and were skipped).
- **`font-src` origins and subsetting.** I read `document.fonts` family/weight/status, not the `src`
  URLs, so I cannot say whether Roboto / YakuHanJP / `midasi-w` are self-hosted or CDN-served. Likewise
  unknown: why `Noto Serif JP` is registered on JP with every face unloaded, and why `roboto-condensed`
  is unloaded on EN `/` and `/news/` — where each would render is not determinable from what loaded.
- **Whether EN's `.dd.update` notched ribbon ever renders.** Rule present; no instance in the 10 sampled
  items. JP's equivalent does render, so the shape is confirmed on at least one site.
- **Shape of the sparkle glyph** in `decoration_base.svg` / `decoration_square.svg`. I read the URLs,
  sizes, opacities and mirroring from the rules but did not open the SVG source, so "sparkle" is an
  inference from the file name, not a verified observation.
- **Not visited / not rendered.** JP `/news/` and `/character/`, EN `/media/`, the external WebStore —
  outside the given stop list. True mobile rendering: viewport was fixed at 1440×900, so the `<900px`
  tier and the hamburger modal were read from CSS rules only, never observed.

### Screenshots

In `./web/`, copied from `C:\Users\exatf\.playwright-mcp\`, viewport 1440×900, `scale: "css"`:

- `en-home-hero.png` — EN `/` KV + fixed blue header, angled Play Now tab
- `en-home-news-section.png` — EN `/` News panel: pink container, skewed tab, card, "View more", diagonal band cut
- `en-home-characters-bento.png` — EN `/` character mosaic + Media tab
- `en-news-listing.png` — EN `/news/`: title parallelogram, 3 filter pills, white cards, green Game pill + date
- `en-characters-grid.png` — EN `/characters/`: 7-col grid, per-record accent, condensed name/VA labels, SVG underline bar
- `jp-home-hero.png` — JP `/` wordmark nav + KV
- `jp-home-news-and-contents.png` — JP `/` two-column: pink news panel with `[追記]` ribbon, two-part bilingual tabs
