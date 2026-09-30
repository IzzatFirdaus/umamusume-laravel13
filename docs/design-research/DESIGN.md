# DESIGN.md: Umamusume Trainer Companion design system

Phase 2 deliverable. Design contract only; no production code was written or modified **at the time of writing (2026-09-27)**. Production components now exist: `stat-band`, `resource-strip`, `guided-step`, `race-calendar`, `grade-point-meter`, `design-preview`, and token-migrated `layout` (see root `DESIGN.md` §3). This line is retained for audit traceability.
Anchors in this file were measured from `docs/game-screenshots/` (see `RAW-FINDINGS.md` §3.2). Ramps were derived from those anchors by linear-light mixing. Every text/background pair used for real copy was contrast-checked; the numbers are printed in §3.4.

Read the root `CONSTRAINTS.md` first. This file does not relax it. The design-side contract derived from this system lives in `docs/design-research/CONSTRAINTS.md`.

---

## 1. What this is trying to look like

A piece of the game's own software, re-cut for a keyboard. Not a fan site, not an anime-themed dashboard, not a SaaS tool with a purple gradient and a racetrack photo.

The client's real character, from the corpus: **a high-key near-white interface with warm brown ink, one yellow-green action colour used with restraint, and shape used instead of shadow.** Everything saturated in it is *semantic*: green means "do this / this is affordable", orange means "this went up", blue means "this went down", gold means "this one is selected", crimson means "this is the failure risk".

The three failures this system exists to prevent:

1. **Dark-mode drift.** The instinct that "game UI" means charcoal and neon is wrong for this franchise, so light is the default. Median frame luminance in the corpus is 193/255 (§1.3 RAW-FINDINGS). A dark surface would be unrecognisable.
2. **Palette drift.** Tailwind's default `green-500` is `#22C55E`, a teal-green. The client's action green is `#7FCC09`, a yellow-green at hue ~87°. Six hue degrees of drift and the resemblance is gone. This is the single most consequential token in the file.
3. **Ornament overload.** The client's ornament (lattice, ribbons, sparkles, arrow-caps) works because it sits on a very quiet field. Copy the ornament without the quiet and you get a bootleg. §7 puts a hard budget on it.

### North star
A Trainer who has spent 400 hours in the client should be able to open this tool, look at the turn timeline, and find the reading effortless, without ever being told what to look at.

---

## 2. Principles

**P1. Light field, saturated meaning.** Surfaces are near-white and quiet. Colour is spent only where it carries information. If a coloured element does not change what the Trainer knows, it should be neutral.

**P2. Preview before commit — informed risk-taking.** The client never lets you fire a decision blind: the Choices panel shows each option's outcome before you accept, and acceptance is a separate action (RAW-FINDINGS §5.1). Every destructive or irreversible-feeling action in this tool gets the same two-step shape. This is also what makes the guided turn flow feel like the game rather than like a form.

The name matters, and it is borrowed: an external mechanics write-up calls this principle **informed risk-taking**, and the phrase is worth adopting because it describes the Trainer's act rather than the UI's (`CONSTRAINTS.md` D-283 governs what else may be taken from that source). A preview whose job is stated as *reducing risk* would soften the number, hide the cost, or recommend a safer option. Its actual job is to make the risk known and leave the decision alone — which is the same boundary P6 draws around predictions. The under-50 Energy caution at the Confirm control (§6.21, D-171) is this principle at the point of commitment rather than a second, kinder preview.

**P3. Numbers are the display voice.** In the client, the biggest and heaviest thing on screen is a number: the turn count, the stat value, the cost. Headlines are modest. Our type scale inverts the web default, where the H1 shouts and the data whispers.

**P4. Shape does the structural work.** Depth and grouping come from stacked silhouettes, ribbons, arrow-caps and notches, not from drop shadows, borders, or blur. See §6.

**P5. Colour never alone.** The client already pairs every state colour with a word (`GREAT`, `Scheduled`, `Hint Lvl 2`). We keep that, which happens to also be the accessibility requirement. A state must be legible to a Trainer who cannot distinguish the hues.

**P6. Deterministic and explainable.** Every number rendered must be traceable to entered turns (`CLAUDE.md` Planner Domain Rules 4 and 5). The UI never shows a figure it cannot point at, no implied recommendations, no simulated forecasts (PRD §6.11).

---

## 3. Colour

### 3.1 Measured anchors

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

### 3.2 Derived ramps

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

### 3.3 Semantic roles

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

### 3.4 The contrast contract

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

### 3.5 Tailwind v4 theme block

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

### 3.6 Stats are not colour-coded, and why that is the right call

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

### 3.7 Dark theme

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



### 3.8 Measured, derived, or decided

Being explicit about which is which, because it affects how much weight the reader should give each line.

- **Measured:** all of §3.1, plus `mood-great` and `mood-good`, and the five dark surfaces in §3.7. The five mood tier **words** are measured too (§6.17); only their three lower pill **colours** are not.
- **Derived:** all ramp steps other than 500, by the mixing rule in §3.2. `mood-normal`, `mood-bad` and `mood-awful` are derived by a second rule, bisection to the mean luminance of the two measured mood anchors, and are provisional (D-259).
- **Decided:** `ink-muted` `#7A7067` (chosen as the lightest warm neutral that still clears 4.5:1 on the panel, so it is not a ramp step), the hue and chroma of the three provisional mood steps (neutral is the safe default for the middle state; the two low states were placed inside measured families rather than given a new hue, per D-26), the page field `#F2F1F8`, and the dark theme's `--color-page`, `--color-idle`, `--color-rule` and ink steps, which are derived from the measured board charcoal rather than measured directly.


---

## 4. Typography

### 4.1 Families

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

### 4.2 Scale

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

### 4.3 The display register

The client reserves one loud typographic voice for celebration: italic, heavy, gradient-filled with an outline ("Rank 19", "RACE FINISHED"). We have exactly one legitimate use for it: **a run reaching `Retired`** (the `RunStatus` enum). It must not appear anywhere else, and it must not be used for a page title. Used twice it stops being a register.

---

## 5. Space, radius, elevation

### 5.1 Spacing
A 4px base with a deliberately shallow ladder, because the client's panels are dense and their rhythm is soft rather than modular:

`4, 8, 12, 16, 20, 24, 32, 48`

- Panel inner padding: 20. Card inner padding: 12 vertical / 16 horizontal.
- Gap between stacked cards: 12. Gap inside a card row: 8.
- The client's page has generous margins around a dense core. Ours keeps 24-32px of page gutter and lets the content breathe inward, not outward.

### 5.2 Radius
From §3.4 of RAW-FINDINGS. `panel 14 / card 10 / chip 8 / badge 5 / pill 9999`. Circles are reserved for the discipline affordance and avatars, and that reservation is meaningful (P4): if everything is round, round stops meaning "pick one of five".

**Radius must nest.** A 10px card inside a 14px panel is right; a 14px card inside a 14px panel reads as a mistake. Inner radius = outer radius minus the padding between them.

### 5.3 Elevation
Three levels, and the cool cast is not negotiable (RAW-FINDINGS §3.5):

| Level | Recipe | Used for |
|---|---|---|
| flat | no shadow, `--color-rule` hairline | rows inside a card |
| lift | `--shadow-lift` | cards and panels on the page field |
| press | `--shadow-press` | buttons at rest; they gain `lift` on hover |
| glow | `--shadow-glow` | selected and focused only |

No blur, no backdrop-filter, no glass. No grey-tinted shadow: the client's shadows carry a violet cast, which is what keeps a light UI from looking like office software.

---

## 6. Component anatomy

Each component names the frame it was read from, so a reviewer can check the claim.

### 6.0 Iconography

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

### 6.0b Fixed glyph assignments

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

### 6.1 Button
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

### 6.2 Panel
`raised`/`panel` fill, radius 14, `--shadow-lift`, padding 20. Header is a capsule (§6.3) that overlaps the top edge rather than sitting inside it.

### 6.3 Capsule header
The client's most repeated element (`Log`, `Scheduled Races`, `Choices`, `Sparks`, `Umamusume Details`; frames `234521`, `230755`, `232345`).
- fully rounded pill, height 44, `title` white text
- a **lattice bleed** at the left end: a `#6ABE01` diamond grid, ~64px wide, clipped by the pill
- the client centres the label; **we left-align it** at desktop widths, because a centred pill over a 900px panel wastes the horizontal space a data tool needs (RAW-FINDINGS §7.3)
- **fill is `green-deep` `#4E7906`, not bright `green`.** A capsule always carries a word, so D-3 applies to it without exception. White on `#4E7906` measures 5.17:1; white on the client's `#7FCC09` measures 1.99:1. The bright green survives in the lattice bleed, the border, the focus ring and the nav tile, which is where the client's recognisable lime actually does its work.

An earlier draft of this section specified a bright `#7FCC09` capsule with white text, copied from the client. That was self-contradictory against D-3 and only surfaced when the token set was rendered in a browser at real size. Recorded here rather than quietly corrected, because the failure mode is the one this system keeps hitting: the client's own chrome is decorative and does not meet a legibility bar meant for a tool read for hours.


### 6.4 Card
`raised` fill, radius 10, `--shadow-lift`, 1px `--color-rule`. Padding 12/16. Selected: `gold` fill with `ink-strong` (8.34:1) plus a 2px `gold` border. Disabled: `disabled` fill, `ink-300` text, no shadow.

### 6.4b Discipline banner

The client's training banner is two stacked ribbons, and the lower one carries information our design had dropped: the **facility activity name**, which changes with the discipline and the level. Observed in the corpus: `Breaststroke` and `Freestyle` for Stamina at the pool, `Incline` for Guts, `Running` for Speed, `Long-Distance Swimming` for Stamina at Lv5, `Dirt` for Power.

```
Stamina Lvl 1        <- pale ribbon, the discipline and its level
Breaststroke         <- saturated ribbon, the activity the facility offers
```

A mockup showing only `Stamina Lvl 1` is incomplete. The activity line is where the game tells you *what this turn actually is*. It is **not** evidence that facility layouts differ between scenarios — every frame carrying these names is Unity Cup, so the list shows variation per facility within one scenario (D-187 carried the stronger claim until 2026-09-27; see `SCREENSHOT-MANIFEST.md`).

### 6.5 Stat band

The core readout (frames `194819`, `202142`, `232345`). Six columns, equal width, in one rounded container:
- header strip, height 26: each column carries its own **subtle tint** from the §3.6 table, with a 1px `band rule` line beneath it, and holds icon plus `label` in `ink`. The tints are deliberately near-invisible individually and only read as a group. Skill Points keeps the cyan tint plus the fact that it has no grade badge, rather than the earlier saturated cyan header, which failed contrast at 2.98:1 with white text
- body: per column, a grade badge (§6.7) at left, `numeral-lg` value, and `/cap` at `meta` beneath
- the value is the largest thing in the band. The label is not.
- **Two markers, two meanings.** The bar carries the **1200 soft-cap line**, where *"training gains for stats beyond 1200 are always halved"* (`CONSTRAINTS.md` D-211), and separately the **scenario ceiling**, expressed as base plus bonus (D-212). They are different facts and must not collapse into one line.
- **Why they cannot collapse: one is a behaviour gate, the other is a maximum.** A stat threshold in this game does not just mean "more"; past 1200 the client runs *different logic* on the stat — gains halve — so the line marks where the rules change. The scenario ceiling marks only the largest value that can be held. Two kinds of fact, drawn apart, because folding the behaviour gate into the bar end would hide the one that changes what a Trainer should do next. The same distinction applies wherever a threshold unlocks a named behaviour rather than a quantity; the thresholds themselves are governed by D-283 and stay untraced until a capture or dataset carries them.
- gain bubbles float **above** the column they apply to (§6.8)
- **the bar's end is the scenario ceiling for that stat, not the app's validation bound.** An earlier revision of this bullet instructed the opposite — cap the bar at 1200 and never imply a wider ceiling — and that instruction is withdrawn: it dressed a schema limitation as a design decision (D-31, `ADR-0002`). The denominators are `1,200 + scenarios.json.stats[i]`, so 1400 / 1300 / 1800 / 1900 per scenario and stat, with **2000** as the recorded hard cap, and a Unity Cup Wit bar and a URA Wit bar at the same value must be visibly different lengths. Where a ceiling has components the tool does not store, §6.22's disclosure line says so rather than silently widening the bar.

### 6.5b Support card rail, failure badge, growth rates

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

### 6.6 Turn chip
The client's torn-page calendar card (frame `194819`), reused as our timeline anchor:
- white body, 2px `blue` outline, radius 10, a `blue` tab strip across the top with two punch holes
- `numeral-xl` remaining-turn count in `blue-700` (5.28:1 with white, and 4.98 against the panel), `label` beside it
- a second, smaller chip stacks under it for the phase countdown
- in the run list this becomes the *identity* of the row: a Trainer scanning runs reads turn depth first, which is what the chip is for in the client too

### 6.7 Grade badge
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

### 6.8 Gain bubble
The scalloped cloud over a stat (frame `194819`):
- white scalloped shape, 1px outline, a small `+5` inside it, and a larger `+22` below the bubble outside it
- our colour rule, reconciled: **the bubble is a preview, the number below is an applied delta.** The preview inside the bubble is `ink-strong` on the white bubble (it is not yet a fact about the run). The applied delta below it follows §3.3: `orange` for up, `blue` for down.
- the client renders the small preview numeral in a red tone. We do not copy that, because in our system `crimson` means training-failure risk and validation error (§3.3), and a preview tinted like an error would misread. This is a recorded departure, not an oversight.
- the bubble is anchored to the stat column it changes, so cause and effect are visually bound (P3, `RAW-FINDINGS.md` §5.3)


### 6.9 Log / timeline entry
Frames `234521`. The pattern the run timeline is built from:
- white card, radius 10, `--shadow-lift`
- a 40px circular avatar **overhangs** the top-left corner, outside the card box. The overhang is what makes the client's log scannable by face; ours uses a turn number disc in the same position, since PRD §6.13 rules out portraits
- inside: `label-strong` title, a hairline rule, then `body` prose
- deltas are written as sentences with the direction word coloured: "Stamina went **up** by **14**" with *up* in `orange` and *14* in `orange`; a decrease in `blue`
- entries group under a **collapsible phase bar**: `green` fill, `title` white text at large size (or `green-deep` for AA), a 24px circular chevron at the right

### 6.10 Choice card (guided input)
Frames `124128`, `124926`. Two-part object, and both parts are required:
- **the option button**: banner shape (§6.1)
- **the preview**: a card beneath or beside it listing every consequence as brown prose with orange/blue deltas, exactly as the client's right-hand panel does
- unselected: preview hidden or muted. Selected: preview at full contrast, card gets `--shadow-glow`.
- **committing is a third, separate action.** Selecting an option never writes data. This is P2 and it is the rule that makes the flow feel like the game.

### 6.11 Skill row
Frame `234521`. Radius 10, idle fill a `sunken`-to-`idle` vertical gradient:
- 40px square icon, own rounded frame
- `label-strong` name, then `body` description ending in a parenthesised distance or type tag at `meta`
- top right: discount/status badge, `orange` fill with `ink-strong` text
- bottom right: cost stepper, grey minus, white numeral pill, `green` plus
- selected: `gold` fill, `ink-strong` text (8.34:1), 2px `gold-deep` border
- `is_unique` skills get a sparkle mark, a `Unique` text tag at `micro`, and a **pink-to-blue gradient chip fill**, matching the client directly (`232345`, where `Blue Rose Closer` carries a pink-to-lavender gradient against flat lavender-grey on the ordinary rows). An earlier revision used a flat `indigo-50` on the grounds that pink and blue were spoken for; that lost the single most legible affordance on the panel, since the gradient is what makes a unique skill findable without reading. The hues here are **material fills, not semantic colours**, and they do not conflict with §3.3 for the same reason a card background does not. `gold` still means *selected* and is still not used here.



### 6.12 Modal
Frame `232345`. Rounded sheet over a scrim. The scrim is a flat 45% `indigo-900` wash; the client does not blur. Full-width capsule header (§6.3), stacked hairline-separated sections, one centred secondary Close button in the footer. Max width 720; a modal wider than that stops being a modal and becomes a page.

### 6.13 Navigation rail
Frames `230755`, `234521`. Vertical rail, 96px wide, icon over `label-strong`, dashed hairline separators. **Active state is a filled tile**: the whole cell becomes `green` with `ink-strong` text, exactly as the client's active Log tile does. No underline, no left border. Inactive cells are transparent with `ink` text.

### 6.14 Form field
The client has almost no text inputs, so this is our extension and it must not look like one:
- label above at `label`, `ink` (not grey-500)
- input: `raised` fill, 1px `--color-rule`, radius 8, height 44, `body` text in `ink`
- focus: 2px `green` border plus `--shadow-glow`
- error: 2px `crimson` border, message at `label` in `crimson` with an icon. Crimson is the only place red appears (§3.3)
- hint: `meta` in `ink-muted`
- number inputs get steppers, matching the cost stepper (§6.11), so the tool's two numeric idioms are one idiom

### 6.15 Energy gauge

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

### 6.16 Advisory and recommendation row

**The advisory is an NPC speech bubble, not a banner.** The client delivers coaching through a circular avatar of a green-clad staff member beside a rounded white bubble (`025519`, `038100`, `038751`, `058814`, `194319`), with a small green `HINT` badge. That is the pattern to adopt, because it converts a warning from an interruption into a piece of dialogue, which is how the game frames every piece of advice it gives.

Adopting it costs almost nothing and buys the single largest immersion win available in this design: the advisory stops looking like validation copy and starts looking like the Trainer's assistant talking to them.

A suggestion is allowed to appear, but it must carry its arithmetic. The accepted form is one short line naming the constant and the stored value that produced it:

> Wit costs 0 Energy and you are at 42. *(GameWith 2026-09-25; source data 2023-02-25)*

Rules: the reason names a number the Trainer can check, the source is cited with its date, and stale constants say they are stale. A suggestion that cannot produce that line does not render. This is how Planner Rule 5's "explainable outputs" survives the scope change recorded in `docs/adr/0001-lift-no-prediction-nongoal-for-energy-guidance.md`.

**When Energy is below 50 the advisory names Wit and Rest specifically**, because those are the two actions the sources describe as the low-Energy response: Wit costs no Energy (§1.1.1) and Rest returns +30 (§1.1.5). The advisory suggests; it never disables, dims or reorders the five options on the basis of a modelled risk (D-155). Rest is offered with its own caveat, since Rest can backfire into 夜更かし気味 (D-205).

### 6.16b Success and failure branches

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

### 6.17 Mood tier

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


### 6.18 Empty, loading, error
Mandatory on every data view (root `CONSTRAINTS.md` C-7, PRD `ARCHITECTURE.md` §7):
- **empty**: a panel with the capsule header, a quiet illustration slot (one motif, §7), a `title` line naming what belongs here, a `body` line saying how to make one, and one primary button. Never a bare "No data".
- **loading**: the panel's real shape with `idle`-filled skeleton rows. The client never shows a spinner over a blank page; it shows the frame you are about to fill.
- **refresh in flight**: catalog reads show last-fetched time from `data_sources` and a quiet progress mark, because the fetch is stale-while-revalidate (`ARCHITECTURE.md` §6). The UI must not imply the data vanished.
- **error**: `crimson` rule on the left of the panel, an icon, the failed source's name, and a retry button. A failed fetch must never blank the catalog (NFR-2), and the UI has to make that visible.

### 6.19 Fan readout

The client states fan progress as a sentence with the shortfall in it, not as a bare number: `Earn 3000 fans` above `Progress 1,943 fan(s) to go` (`Screenshot 2026-07-14 194819.png`). That is the shape to copy, because it answers "how far am I" without the Trainer doing subtraction against a target they have to remember.

- Persistent in the run header beside the Energy gauge, because fans gate entries and a Trainer checks it before deciding a turn.
- Current total at `numeral-md`, tabular, `ink-strong`.
- Target and shortfall as one `body` line, the shortfall in `up` orange, since fan gain is the reward axis of this tool and orange already means increase.
- No progress bar. The client does not draw one, and a bar implies a linear race to a fixed ceiling when the real calendar has several thresholds stacked.

### 6.20 Race calendar

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

#### Lock state grammar, the three that matter

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

### 6.21 Run header, the persistent resource strip

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

### 6.22 Scenario identity and stat caps

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

### 6.23 Scenario-composed resource strip

The strip is the mechanism by which the app becomes scenario-aware rather than scenario-decorated (§6.22, `CONSTRAINTS.md` §10n). It sits in the run header below the identity row and holds **zero to five** items: always turn, Energy, Fans; then per scenario Team Rank and Spirit Burst count, or Grade Points and Shop Coins.

- **Composition rule.** Items are declared per scenario and rendered in a fixed priority order — clock, then the scenario's own currency, then Fans — so the strip's *shape* changes and its *positions* stay stable within a scenario. A Trainer running two Unity Cup careers sees the same layout; a Trackblazer run does not inherit the Team Rank slot and find it empty.
- **An empty slot is never rendered.** A reserved-but-blank gauge tells the Trainer the mechanic exists and they have none of it. Absent beats empty, and this is why the strip must be flexible-width, not a fixed grid.
- **One flex row, no fixed widths.** Numerals range from three digits (`450`) to six (`120,000`) and labels from `Energy` to `Grade Points`. The container must therefore be `flex` with `gap`, never `grid-cols-5`, or a Trackblazer Grade Point count and a five-figure fan count will collide at 1280px. This is the specific layout risk the scenario work introduces and the reason the strip is its own component rather than header chrome.
- **Baseline is a real configuration, not a fallback.** A URA Finale run shows three items and that is correct — the scenario has no team system, no shop and no currency. An undescribed fourth scenario renders these same three plus its own caps (§10n, D-241).

### 6.24 Unity Cup: Team Rank gauge and Spirit Burst roster

**Team Rank** is a letter, not a meter: `G F E D C B A S S+`. It is rendered as a letter chip per stat plus one aggregate rank, because facility level derives from the *per-stat* rank while the "It's On!" reward derives from the *aggregate* — two different consumers of one widget, and the reason `S+` needed its own visual step even though facility level stops rising at `S` (§6.22 marker, D-222).

**Spirit Burst roster** is a compact list of teammates, each carrying one of **six** states: chargeable, charged, held, normal-spent, **Extreme-chargeable**, Extreme-spent. Requirements the states impose on the anatomy:
- normal and Extreme must be distinguishable **at a glance and by text**, since Extreme is the higher reward and the 0%-failure guarantee — a colour-only difference fails P5 and D-6.
- a spent-normal teammate is *not* inert: they are the next Extreme candidate. Any "consumed" treatment (reduced opacity, strikethrough) is a lie on the fifth state.
- **no energy penalty is depicted on Special Training.** The pre-2026-07-01 rule that bursts raise energy cost except on Wit is dead; the only surviving energy interaction is a Wit burst granting **+5 extra recovery** (§6.15, D-223).
- the burst counter in the strip counts **normal plus Extreme combined**, because that is what the reward ladder reads (D-223).
- where an Extreme burst or a Good-Luck Charm sets a facility's failure rate to zero, the facility's risk affordance is **removed**, not downgraded to "low" (D-231).

**Occupancy is content.** Facility tiles show how many teammates are present, because the multi-uma bonus scales across 2/3/4/5 flames and that number is the reason to prefer a crowded tile over a 1-on-1. The client's Unity Training overlay hides skill-hint icons underneath; the tool must surface a hidden hint rather than reproduce the collision (D-229).

### 6.25 Trackblazer: Grade Point meter, shop counter, Epithet tracker

- **Grade Point meter** reads against the *current* objective only, with the deadline date and a standing note that surplus does not carry forward. A cumulative total would advertise a banking strategy the rules forbid (D-232).
- **Shop counter** pairs the balance with **turns until the rotation resets**, and the balance is the lesser of the two. The in-game cap of five copies per item and the overwrite hazard — a weaker multi-turn item bought while a stronger one is active destroys it — are both purchase-time facts, so they belong in the shop step, not in a tooltip nobody reads (D-232).
- **Epithet route tracker** is a won-of-needed checklist per route (`2 of 3`), because routes are what shape the racing schedule and the rewards are stat and hint gains. Keep it as a list, not a grid of cards: there are dozens of epithets and only a handful of live routes, and an equal-sized card wall is the §6.15-of-layouts mistake.
- **Race Fatigue chip** shows consecutive races run and its consequence. It is the one place a real percentage may print, and it must vanish after Late December (D-230, D-231).
- **No Race Calendar.** Its absence is the scenario's defining UI fact. The slot is filled by the Grade Point meter and a free-form race log (D-221).

### 6.26 Rarity vocabulary must not collide with state colour

The scenario guides describe rewards as **gold-rarity** and **white-rarity** skill hints, and `pick` (`#EFC96A` / dark `#F5B73C`) is already the **selected** state in this system. Reusing it for "gold rarity" would make every rewarded item look pressed and every selected row look like a prize, and the collision is invisible to anyone not reading the tokens.

So: rarity is written as a **word**, never as the selection colour. If a visual marker is needed it is a neutral outline or a rank letter, and the selection treatment stays exclusive to selection. The same test applies to the client's own gold-text cap-increase signal (D-213): that one is legitimate because it marks a *value*, sits inline in numerals, and never appears on an interactive surface — three conditions the rarity case fails.

**Scenario currencies get no identity colour.** Grade Points, Shop Coins, Team Rank, Result Pts and Spirit Burst counts are glyph-plus-label-plus-numeral, matching how the five stats are already handled (§3.6, D-114). Each new currency could claim a hue; four of them would blow the dose cap in §7.1 and none of them carries meaning that colour could add, since the Trainer's decision depends on the *number*, not the pigment.

### 6.27 Legacy Select

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

### 6.28 Run Completion state

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

## 7. Motif and ornament budget

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

### 7.1 Colour dose caps

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

## 8. UX architecture

Full layouts, states, and acceptance conditions are specified in `docs/design-research/CONSTRAINTS.md` §4-§7, which is the reviewable contract. This section records the intent behind each.

### 8.1 Trainer's Dashboard (Screen A)
The client's own two-window composition (scene left, working panel right) is the frame. Left: the run identity and the persistent stat band, which is always on screen because a Trainer reading a turn needs the totals the way the client keeps them on screen during training. Right: the turn timeline as a stack of §6.9 entries, grouped into collapsible phase bars, newest last. The turn chip (§6.6) anchors the top-left and is the persistent "where am I" object.

The timeline is the product. Everything else on the dashboard exists so a turn can be read in context: the stat band shows where the series landed, the skill panel shows planned-versus-actual (US-4), and provenance sits at `meta` weight because it is a fact about the catalog, not about the run.

**The question the dashboard answers is: "how did this stat get to where it is?"** That is why the primary visual is a turn-ordered entry list with per-turn deltas, not a line chart. A chart of six series over 24 turns answers "where did the curve go", which is a question a Trainer cannot act on: they already know the shape, they want the turn that caused it. The stat band plus the delta-bearing timeline answers the real question, and no chart ships on this screen unless it is retitled to a question a line actually answers (`CONSTRAINTS.md` D-95).


### 8.2 Guided Turn Input (Screen B)
This is where the brief's "not a web form" requirement lands, and the client already contains the answer: the Choices panel. A turn is logged as a sequence of decision cards, each one asking a single question in the game's voice ("Which training are you focusing on this turn?"), each showing its consequence preview, and each committing only on an explicit confirm.

The critical property: **the preview is the input.** The Trainer is not filling in six number fields and hoping; they are picking the thing that produced the numbers they can see. The fields exist as an escape hatch for paste-and-correct, not as the primary path.

Energy enters this flow as a **logged value plus an advisory row**, not as a gate. The gauge is persistent in the header so the Trainer knows their position before choosing, and when stored Energy is under 50 the step shows the §6.17 advisory naming Rest, Outing and Wit as the game's three documented mitigations. What the flow must not do is block, dim, or auto-reorder the five options on the basis of a modelled risk: that turns an advisory into a prediction the sources cannot support. The Trainer picks; the tool explains.

**No numbered step sidebar.** A vertical 1-2-3-4 rail with a highlighted current step is the shape of a SaaS setup wizard, and it is the single fastest way for this flow to stop feeling like an in-game event. The client has no such rail: an event screen presents one question and its options, and the only progress cue is the turn counter. So the flow carries a single `Step 1 of 3` line with three dots at the foot of the card stack, and nothing else. Where the steps genuinely need naming for orientation, they are named *inside* that line, not in a column beside it.

**Deck awareness, and the hard limit on it.** The discipline step asks "Which training are you focusing on this turn?", and a Trainer is answering that question with their deck in mind: which cards sit on that tile, whether their gauges are past 80, and whether the tile is where a card's own discipline is. So the option row carries the deck's chips for the chosen discipline — the §6.5b rail's data, reused here rather than redrawn — because the deck is the reason the choice is good, and a step that asks the question while hiding the answer is a form, not a decision.

What the step must not do is turn that into a recommendation. Three of the inputs to "which tile should I pick" are **not stored anywhere in this application**: the per-card friendship gauge at the start of the turn, the Specialty Priority roll that decides whether a card actually appears on its own tile, and the per-tile participant count. Each is either Trainer state the tool never records or a random draw the tool must not simulate (Planner Rule 4, PRD §6.11). So the deck row is **descriptive and inert**: it names the cards whose discipline matches the option, and it stops there. No ordering of the five options by deck strength, no "recommended because three cards are here", no highlighted option. If the owner later accepts `ADR-0005` option 2 and the deck becomes stored data, the row may add counts, and still no ranking, because the roll underneath it is not modelled.

### 8.3 Run list (Screen C)
Cards, not a table, because the turn chip wants to be an object. Each card: trainee name (`title`), scenario, status pill, turn depth chip, the **micro-grade strip**, and the skill plan tally as Suggested/Acquired/Skipped counts. Sort and filter are `label`-weight controls in the panel header, never a toolbar that outshouts the data.

**The micro-grade strip** is what makes a six-card grid scannable. A Trainer deciding which run to open is asking one question, "how good is this build", and reading five four-digit numbers per card to answer it does not scale.

- Five 18px grade chips in the fixed stat order Speed, Stamina, Power, Guts, Wit, no values, no labels. The order is the only thing that identifies which chip is which, and it never varies.
- Each chip carries the §6.7 grade letter at `micro` in the §3.6 band tint as its background, so the strip also repeats the column identity used on the dashboard.
- A 4px dot inside the chip's lower-right corner marks a stat that has broken past 1000, because that is the threshold a Trainer actually cares about and it is derivable from the stored values.
- The strip is `aria-hidden` and the same information is offered as text to assistive technology: the card carries a visually-hidden line reading "Grades: Speed D, Stamina D, Power E+, Guts D, Wit F." A pattern that only works by sight fails P5 and D-12.
- Values stay on the card at `numeral-md`, below the strip. The strip is a shortcut, never a replacement.

The strip is deliberately not a sparkline and not a radar chart. Both answer "what shape is this build", which is not the question at this size, and a chart in a card is the `CONSTRAINTS.md` D-94 failure with extra steps.

### 8.4 Skill search (Screen D)
FR-D-2 asks for autocomplete on normalised keys. The client's skill row (§6.11) is already a search result, so the search surface is a list of those rows under a single field. Matching is on `match_key`, so a query for a Japanese name can return an English row; the result must show which field matched, because a Trainer needs to know whether they found the skill or found an alias of it.

---

### 8.5 Dynamic event resolution

A turn is not always "pick a training". Five things can happen, and the UI must present each as itself rather than as another form variant. The classification below is the design's own; the sourced backing for each row is stated, and where the source is thin the design stays generic on purpose.

| Flow | Trigger | What the screen does | Source backing |
|---|---|---|---|
| **F1 Standard turn** | No event fired | The five banner options, preview, confirm | `UMAMUSUME_REFERENCE.md` §1.1.2 |
| **F2 Character event** | Round-triggered or random, specific to the trainee | Event panel slides in over the scene: narrative, then A/B/C choices each with previewed deltas | `training_events__char.json`, 135 chains (§1.4.4) |
| **F3 Support card event** | Trainer occupied a tile with a card on it | Same panel shape; the header names the card and the outcome list adds Bond and hint effects | `training_events__friend.json` (11 Pal), `training_events__group.json` (5 Group) (§1.4.4) |
| **F4 Scenario event** | Mandatory, scripted by the scenario | A single-choice panel, or a multi-step scenario sheet when the event genuinely has parts | §1.1.2, §1.6.1, described qualitatively only |

**Random world events are not modelled as a separate flow.** No in-run dataset in the reference supports a category distinct from the three chains above, and §3.4 "recurring event types" is live-ops content on a different axis entirely. If such events exist they land in F2 or F4, and the `TurnEvent` row carries a free-text origin so the data does not lie about a taxonomy it does not have.

### 8.6 The event panel

F2 and F3 share one component, and it is the client's own object (`Screenshot 2026-07-14 124128.png`, `124926.png`), not a web modal:

- **The scene stays.** The panel is an overlay on the left region; the training scene, the turn chip and the stat band all remain visible behind it. A scrim that blanks the page destroys what the client is careful to preserve, which is the Trainer's sense of where in the run they are.
- **Tag ribbon, not a title bar.** A cyan parallelogram tag names the origin (`Support Card Event`, `Character Event`), and a wider blue gradient ribbon below it carries the event title in white bold. Both are slanted at one end.
- **Narrative block** in `body` at generous line height, with the speaker named above it.
- **Choices are §6.1 banner buttons**, and each carries its own preview showing every consequence before commitment. Amended 2026-09-28: the old clause "never radio inputs" was written when a radio meant a row of form widgets, and it is now the opposite of what the component needs. The built rail renders `<input type="radio" name="choice">` inside the banner, so the group tells the truth about itself (KI-14, `slice-5-2026-09-28.md` §5): the banner is the styling, the radio is the semantics, arrow-key roving and selection are the platform's, and a choice reaches the request on submit, which is what makes the two-stage preview work without a script. What stays banned is a bare radio row where a decision card belongs, and a `role="radio"` claim on an element that does not implement it.
- **Delta colours are the client's**: gains orange, losses blue, never green and never red.
- **A cap increase is never phrased as a stat increase.** The client separates raising a value from raising its ceiling: choice previews read `Max Energy +4` against `Guts +10` (`124128`), and §1.3.4 names the effect class 「限界値アップ」, Global `Max Speed`, `Max Stamina`. A log line reads `Speed cap went up by 4`, never `Speed went up by 4`. Exact client log phrasing is unobserved, so the wording is ours; the distinction is not negotiable.
- **Confirm is a separate action** and is the only thing that writes.

### 8.7 Scenario sheet

F4 is the one place a stepped flow is allowed, and only when the event genuinely has multiple parts. A scenario sheet may show a step list; a standard turn may not. The sheet is a wider version of the same panel with the scene still behind it, and it keeps the tag ribbon, the banner choices and the preview-before-commit rule. It does not become a wizard with a progress rail and a Back / Next footer, because that shape signals software onboarding rather than a game beat.

The sheet must be **data-driven, not scenario-specific**. The summer camp mechanic is disputed between the brief and `UMAMUSUME_REFERENCE.md` §1.1.2, which says every discipline is set to Lv5 at once for the four-turn window rather than the Trainer picking two. Encoding either version into the UI would bake an unverified rule into the interface, so the sheet renders whatever steps and options the scenario record supplies and the mechanic question is raised separately rather than answered by a wireframe.

### 8.8 TurnEvent, and what it costs

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


### 8.9 Race selection, F5

A fifth flow, added because the race calendar is a decision point and not just a display. It appears **only on a turn where the calendar holds an entry**, and it reuses the §8.6 event-panel shape rather than introducing a new component: same banner buttons, same arrow-caps, same preview-before-commit.

The step is **conditional**. It renders only on a turn where `scenario_races` holds an entry for that slot; on every other turn the flow goes straight to training or rest. A race step on an empty slot is noise that trains the Trainer to ignore it.

Each race option shows: race name, tier badge, `fans_needed` against the current fan total, whether it is mandatory, and the Energy cost. A fan-locked race is visible but disabled with its number shown, never hidden, because a race the Trainer did not know existed cannot be planned for. The preview lists the fan gain and the placement-dependent outcomes. **Skip is a first-class option**, rendered as a banner like any other, because "I am choosing not to run this" is a decision the Trainer makes deliberately and it should not be the absence of a click.

The panel must not rank races, project a placing, or estimate a win. `PRD.md` §6.11 still forbids race prediction and this flow does not change that; the Energy and `TurnEvent` decisions in ADR-0001 were scoped to training, explicitly leaving race outcomes untouched. Eligibility is arithmetic over stored values and is fine. Outcome is not, and does not appear.


---

## 9. Motion

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

## 10. Accessibility

- Contrast: every text pair in §3.4 passes at the stated level. The chrome steps are not used for text. This is the direct response to the client's own failing chrome (§3.4) and it is the one place we knowingly depart from the source.
- Focus: 3px `green` ring at 35% alpha, always visible, never `outline: none` without a replacement. The ring is bright green because it is chrome.
- Target size: 44px minimum on anything clickable. The client's discipline buttons are 90px; ours are 64px, which is generous and leaves room for five in a row.
- Colour-independence: enforced by P5. Every state carries a word. Grade badges show the letter, not just the hue. Deltas show the sign and the direction word, not just the colour.
- Keyboard: the guided flow is fully operable without a pointer. 1-5 pick a discipline, Enter confirms, Escape steps back one card. The turn timeline is a list with roving focus so a Trainer can read it with a screen reader in order.
- Screen readers: the stat band is a table or a list with explicit labels, not a row of divs. Gain bubbles are `aria-live="polite"` so a change is announced without stealing focus.
- The faceted page field and the lattice bleed are decorative CSS and are not exposed to assistive technology.

---

## 11. Open decisions

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

## 12. Review log

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

## 13. Scenario-aware mockup review, v9 round

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

## 14. Legacy and Run Completion mockup review

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

## 15. External review pass, claims versus citations (2026-09-27)

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
