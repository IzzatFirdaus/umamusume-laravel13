# RAW-FINDINGS: visual research for Umamusume Trainer Companion

Phase 1 output. Research and documentation only; no production code in this session.
Compiled 2026-09-27. Every colour value below was measured from a real file in this repository, not chosen.

Sources of evidence:
- `docs/game-screenshots/` (1,160 PNG frames, captured 2026-07-14 to 2026-07-18)
- `docs/UMAMUSUME_REFERENCE.md` §1.1, §1.3, §6 (Global client terminology)
- `PRD.md`, `ARCHITECTURE.md`, `ARCHITECTURE-ESSENTIALS.md` (data model the UI must map to)
- Official web properties, section 6 below

Scripts and intermediate data live in `docs/design-research/_scratch/` (untracked scratch): `triage.py`, `cluster.py`, `sheet.py`, `probe.py`, `probe2.py`, `accents.py`, plus `clusters.json`, `accents.json`, `colorprobes.json`, `colorprobes2.json`.

---

## 1. Corpus triage

### 1.1 What the filenames tell us (and what they don't)

All 1,160 files are named `Screenshot YYYY-MM-DD HHMMSS.png`. There is **no semantic naming**, so category assignment had to come from image content, not from the path. The corpus is Windows Snipping Tool output: several frames visibly include the "Screenshot copied to clipboard / Snipping Tool" toast, which explains the frame-size spread.

Capture sessions, by date:

| Date | Frames | Character of the session |
|---|---|---|
| 2026-07-14 | 347 | Training runs, story events, choice panels |
| 2026-07-15 | 639 | Longest session; dense event/choice capture |
| 2026-07-17 | 77 | Full-desktop frames: menus, modals, calendar |
| 2026-07-18 | 97 | Full-desktop frames: race results, career profile |

### 1.2 Frame taxonomy by dimensions

Frame size is the cheapest reliable proxy for capture intent, and it splits the corpus three ways:

| Bucket | Frames | Distinct after dedupe | What it is |
|---|---|---|---|
| `1920x1080` full desktop | 173 | 44 | Two-window captures: game client at half width plus a side panel. **Highest design value** (shows chrome, layout, and panel relationships together). |
| `~1200-1570 x 470-1080` | 96 reps in the wide set | 96 | Large region snips: event scenes with the Choices panel, race screens. |
| `~560-880 x 250-1080` | ~891 | ~611 | Portrait region snips, mostly the **training HUD** and small modals. |

Perceptual dedupe (dHash, Hamming threshold 6, 60-frame rolling window) collapsed **1,160 frames into 751 distinct screens**. The largest single cluster holds 58 frames (`Screenshot 2026-07-18 001811.png`) and the next 14, 14, 7, 7, 6, 6: the corpus is heavily redundant, which is why sampling beats enumeration.

### 1.3 Global luminance finding

Median frame luminance is **193 of 255** (min 86, max 246). The dominant-colour pass over the UI-heavy frames returns panel whites in the `#F5F3F8`–`#FAF9FC` range covering 40-60% of the frame.

**This is the single most load-bearing research result in this document.** The instinct for a "game UI" is a dark, glassy, neon-trimmed surface. Umamusume's client is the opposite: a **light, near-white, high-key interface** with saturated accent colour used sparingly and semantically. A dark-mode-first design would be unrecognisable as this game. See §7.1 for the consequence.

---

## 2. Screen inventory

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

## 3. Visual language extraction

### 3.1 Surface and material

The client is **not** glassmorphic. Measured properties:

- Panels are **opaque near-white**, `#F7F6FA` to `#FAF9FC`, with no backdrop blur over the scene except where the game deliberately dims the whole frame behind a modal (S5).
- Edges are defined by **form, not by border weight**. Where a border exists it is thin (1-2 px) and low-contrast: a pale grey-violet on white cards, or a saturated colour only on an interactive element.
- Depth comes from **stacked silhouettes**, not from shadow spread. The discipline banner (S1) is two ribbons at different offsets; the choice button (S2) is a white body with a coloured arrow-cap; the stat bar is a coloured header band sitting on a white body. Each component is built from 2-4 overlapping flat shapes.
- The **page background** is a pale lavender-white carrying a low-poly faceted diamond pattern (clearly visible behind the Scheduled Races panel in `230755` and `235229`). It is a texture, not a gradient, and it reads as soft crystalline paper.
- Glossy highlight: buttons and pills carry a **hard-edged diagonal sheen** in their upper-left, visible on the Close button (S5), the Save button (S10), and the Confirm button (S4). This is a stylised plastic/enamel sheen, not a physical material simulation.

### 3.2 Colour, measured

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

### 3.3 Typography

Font files are not extractable from screenshots, so this is a shape reading, not a family identification. Section 6 supplies the measured web-font answer for the official sites.

- **Body and labels**: a rounded humanist sans. Terminals are open and soft; the lowercase `a` is double-storey; counters are wide. It is close in personality to a rounded grotesque, and it is used at a single medium weight for most UI text.
- **Numerals are the display voice.** Turn counts, stat values, costs, and rank numbers are set much larger and heavier than their surrounding labels, and the big ones (Rank 19, the `328`/`329`/`279` stat row) carry a **gradient fill plus a thin dark outline**. Numbers, not headlines, are what the eye lands on.
- **Headers inside panels** are set at only a modest step above body size and are centred within a coloured capsule (Log, Scheduled Races, Choices, Sparks). There is no large page title anywhere in the client.
- **Italic display forms**: "Rank 19", "RACE FINISHED", "Received Result Pts!" are italic, heavy, and gradient-filled with an outline and a drop shadow. This is the celebratory register and it is used rarely.
- **Japanese text** sits alongside Latin in the JP client at the same scale; the Global client drops it. Our tool shows both (`name`, `name_ja`), so the type system must keep a CJK-capable fallback that does not change the Latin metrics.
- Letter-spacing on Latin labels is slightly open; `text-transform` is never used to shout. Even section headers are sentence case ("Scheduled Races", "Umamusume Details", "Skill Points").

### 3.4 Shape and radius grammar

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

### 3.5 Depth and shadow

- Cards on the pale background carry a **close, soft, cool shadow** (roughly a 0 2-6 px blur at low alpha with a blue-violet cast, not a grey one). It lifts the card without separating it from the page.
- Stacked ribbons create depth by **offset and overlap**, with the lower ribbon slightly darker.
- The selected skill row and the selected photo filter use a **coloured outer glow** rather than a bigger shadow (`134106` shows a bright green ring plus bloom on the selected thumbnail).
- There is no glassmorphism, no inner shadow, and no neumorphism anywhere in the corpus.

### 3.6 Iconography

- Discipline icons are **small 3D renders** (running shoe, heart, dumbbell, horn, book) sitting inside a flat coloured shape. They are glossy and miniature, not line icons. A line-icon set placed next to them reads as a different product.
- The five stat icons double as the stat's identity across the whole UI: shoe / heart / dumbbell / flame-horn / book, and the same glyphs head the stat band, the growth-rate pills, and the discipline buttons.
- Rarity and rank are expressed with **stars** (`★★★` with hollow unfilled slots) and **laurel-wreath letter badges**.
- The `!` in a small circle marks an info affordance; it appears top-centre on the scene header and next to the turn counter.
- A **double chevron** (`»` rotated to point down, in yellow) marks "this is the boosted option", under the active discipline button and on the support-card rail.
- A small **flame** badge marks a support card sitting on its own training tile; a **rainbow arc** marks friendship training available.

### 3.7 Motifs

Recurring decorative vocabulary, all of it present in the frames rather than inferred:

1. **Argyle / diamond lattice** in the left end of green capsule headers (`234521`, `235229`, `232345`). Measured as a darker-green diamond grid inside a `#6ABE01` field.
2. **Low-poly faceted page background** in pale lavender.
3. **Sparkle/star burst** on skill icons and on the acquired-skill state (the blue four-point sparkle in S4).
4. **Laurel wreath** around rank letters.
5. **Ribbon and banner forms** with slanted ends, used for anything that names a thing (event title, discipline, phase).
6. **Double slash** marking a selected or active row.
7. **Number-cloth stripes** on the calendar tab bar.
8. **Radial glow** behind the Umamusume portrait.

### 3.8 Layout grammar

- **Two-window desktop composition.** The client is used on PC with a scene panel at roughly 50% width and a working panel beside it. Every full-desktop frame in the corpus is exactly this. Our tool is a desktop-only web app (PRD §2), so this is a directly transferable layout fact, not a metaphor.
- **Persistent right rail** for top-level destinations, icon over label, dashed separators, active = saturated tile.
- **Header capsule centred over the panel body**, with the lattice ornament bleeding from the left edge.
- **Segmented pill tab bar** for peer views (Junior / Classic / Senior Year; Conditions / Skills).
- **Dense 4-column card grids** for time-series data (the race calendar is a 24-cell grid of half-months).
- **Stat readouts are a horizontal band of equal columns**, each column stacked as: label / grade badge + value / cap. Six columns including Skill Pts, which is visually separated by its own teal header.
- Gains are shown **above** the number they will change, in a cloud-shaped bubble, not inline and not in a tooltip.

---

## 4. Component anatomy, as observed

### 4.1 Turn indicator (S1)
A torn-page calendar card: white body, blue outline, a blue tab strip across the top with two punch holes, the remaining-turn numeral in large blue, and "turn(s) left" set in small text beside it. A second, smaller card of the same shape stacks underneath for the phase countdown ("8 turn(s) Until the Unity Cup"). This is the strongest "where am I in the run" affordance in the client and it is a *card*, not a progress bar.

### 4.2 Energy gauge (S1)
A fully rounded track with a dark charcoal unfilled remainder and a **segmented** fill running cyan → green → olive, so the bar reads both level and threshold at once. Label "Energy" sits in its own small cream pill to the left of the track.

### 4.3 Mood pill (S1)
A saturated rounded pill with an up-arrow glyph and a single uppercase word: `GREAT` (pink `#FB5590`), `GOOD` (orange `#ED8036`). Five states exist in the game (`UMAMUSUME_REFERENCE.md` §1.1.6); the pill carries both colour and word, never colour alone.

### 4.4 Stat band (S1, S5)
Six equal columns. Each: a coloured header strip with icon + label, then a white body holding a grade badge, a large value, and a small `/cap` beneath. Skill Pts is separated by a teal header and has no grade badge. Gain previews float **above** the band in scalloped white bubbles with red numerals, plus a larger orange delta under the bubble.

### 4.5 Choice card (S2)
Two-part object. The **button** is a white body with a thin accent outline and a gradient arrow-cap at the right end, an accent-coloured circular icon at the left, and brown bold label text. The **preview** sits in the side panel: unselected it is a white card listing each outcome as brown prose with orange/blue deltas; selected it becomes a solid action-green banner with white text and a double slash. Selecting does not commit; a separate Confirm does.

### 4.6 Log entry (S3)
White rounded card. A circular avatar **overhangs** the top-left corner, outside the card's box. Inside: name in brown bold, a hairline rule, then body prose. Deltas are written as sentences with the direction word colour-coded. Consecutive entries are grouped under a **collapsible green phase bar** carrying a scenario logo and a circular chevron.

### 4.7 Skill row (S4)
Rounded rectangle, idle fill a lavender-grey vertical gradient. Left: square icon with its own rounded frame. Centre: name (brown bold) then description (smaller brown) ending in a parenthesised distance tag. Right-top: an orange discount badge reading "Hint Lvl N / NN% OFF!". Right-bottom: a cost stepper of grey minus, white numeral, green plus. Selected state swaps the whole row to a gold gradient.

### 4.8 Modal (S5)
Rounded white sheet over a dimmed scene. Full-width green header capsule with lattice bleed and a centred white title. Body is stacked sections separated by hairlines. Footer has a single centred Close button, white with a thin dark outline and a diagonal sheen.

### 4.9 Date cell (S6)
Rounded rect in one of three measured states: unavailable `#D0D1D0` with a muted plus; available white with a green plus; current pale yellow with a green plus and a warm outline. A race occupying the cell replaces the plus with a small artwork thumbnail wearing a pink `Scheduled` or red `Goal` flag.

---

## 5. Interaction and state patterns worth carrying

1. **Preview before commit.** Every consequential choice shows its outcome numbers before it is accepted, and acceptance is a separate action. This is the core of the "Guided/Contextual Input" brief and it is native to the client.
2. **Colour always paired with a word.** Mood, availability, and acquisition state each carry a text label alongside their colour. This is both the aesthetic and, conveniently, the accessible implementation.
3. **Numbers live above the thing they change.** The gain bubble floats over the stat, so cause and effect are visually bound.
4. **One saturated tile marks the active destination.** The nav rail's active state is a filled block, not an underline.
5. **Grouping by phase, collapsible.** The Log groups turns under a named phase bar, which is exactly the shape a 24-turn run needs on screen.
6. **Circular buttons for the five disciplines, rectangles for everything else.** Shape carries meaning: round = pick one of the five.

---

## 6. Web cross-reference

Measured from the official properties on 2026-09-27 through a real browser, because both sites are JS-rendered SPAs and a plain fetch returns an empty shell. Full data with per-element values: `_scratch/WEB-FINDINGS.md` (390 lines) and seven 1440x900 captures in `_scratch/web/` (`en-home-hero`, `en-home-news-section`, `en-home-characters-bento`, `en-news-listing`, `en-characters-grid`, `jp-home-hero`, `jp-home-news-and-contents`). The study was run twice independently and returned the same core measurements, which is the strongest evidence in this document for the Roboto and no-gradient findings.

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

### 6.1 What the web study corroborates

Four devices I had derived from the client turn out to be shared brand grammar, which raises confidence in them materially:

1. **`clip-path` notches and angled edges.** `.playnow a` uses `polygon(13px 0, 0 100%, 100% 100%, 100% 0)`; `.playnow-label` and `dd.update` use ribbon-notch polygons. This is the same silhouette family as the client's arrow-cap choice button and notched Back button. The arrow-cap in DESIGN.md §6.1 is now supported by two independent sources.
2. **`skew(±15deg)` tabs with counter-skewed text.** Present on both sites. Corroborates the parallelogram discipline banner and event tag.
3. **Sparkle texture painted onto buttons via `::before`/`::after` SVG** (`decoration_base.svg`, `decoration_square.svg`). Corroborates the button sheen rule and the sparkle motif.
4. **Double-chevron affordance** and the `teitetsu` bullet emblem recur on both. Corroborates §3.6.

The absence of glass and of blur is confirmed on both surfaces, so the "no glassmorphism" rule is now backed by the client *and* the web.

### 6.2 What the web study contradicts, and the ruling

**The font.** Cygames' web is Roboto. The client is rounded. DESIGN.md §4.1 mandates rounded and bans Roboto, so the web evidence runs against the primary type decision.

The ruling stands, deliberately: the tool's job is to feel like the software a Trainer already spends hours inside, which is the client. The web property is a marketing site with different constraints (photography, brand campaign art, a fixed header over full-bleed imagery). Adopting Roboto because Cygames does would be copying the wrong artifact. The counter-evidence is recorded in DESIGN.md §4.1 and flagged as an owner decision rather than hidden.

Two things *do* transfer from the web's typography and are adopted:
- **Weight and slant as the hierarchy device.** On the Global site the rule is `700 + italic = label`, `400 + roman = content`, with letter-spacing at 0.02/0.05/0.08em tiers and `text-transform` never used. That is a disciplined system, and it is exactly the mechanism behind the celebratory italic register in DESIGN.md §4.3.
- **`text-transform` is not used on the EN site at all.** Case is authored. Consistent with §4.2's rule.

**The brown ink.** `#4d4d4d` on both web properties, not brown. Same ruling: the client's brown is measured at 18.9% coverage of UI frames and is what makes its light UI feel friendly rather than clinical. Kept.

**The second green.** `#b5d913` is 13 degrees yellower and considerably lighter than the client's `#7FCC09`. Not adopted; the client value is measured from the surface we are imitating. Worth knowing that a Trainer who has only seen the website will find `#b5d913` more familiar than `#7FCC09`, and that this is the kind of detail that decides whether a theme reads as "this game" or "a fan site about this game".

### 6.3 One pattern the web has that the client does not, and we should steal

**Category colour doing triple duty.** On `umamusume.com` a single `--color-category` per record drives the chip fill, the timestamp ink, and the colour of the "Details" affordance. On the JP site it is set inline per card. It is how both sites get identity in the content area without logos.

That maps cleanly onto this tool: a run's `status`, a skill's `type`, or a source's `source_key` could each own one accent that colours its chip, its timestamp, and its open-affordance together. It is cheaper than a per-record illustration and more coherent than colouring each element independently. Recorded as a candidate for the run list and the review queue; not yet in DESIGN.md, because it needs a contrast pass per theme first (CONSTRAINTS D-103).

### 6.4 Layout note

Neither web property is a desktop-fixed canvas: EN is `vw`-derived off 1440 with three width tiers breaking at 900 and 2000, JP sizes in multiples of 3.75px off a 15px root. Both are responsive. This does not change PRD §2's desktop-only posture, but it does mean the two-region frame in DESIGN.md §8.1 stacking below 1024px is consistent with how the franchise behaves on a narrow window.

### 6.5 Limits of this study

- The first study pass failed to capture `/characters/` on a tool timeout; the second pass captured it. `roboto-condensed` is registered but *unloaded* on `/` and `/news/` and loads only on `/characters/`, so the condensed finding is scoped to character name and VA labels, not to the site generally.
- The header blue on EN is a bitmap (`bg_blue.Ctkr-Cw2.jpg`) behind a transparent bar, so its exact hex was not sampled. The declared token is `#2a5dfa`.
- `prefers-reduced-motion` was not exercised; motion values are read from `transition` declarations, not observed.
- Fonts were identified from `@font-face` registration and computed `font-family`, which is reliable for family but says nothing about whether Cygames licensed a modified cut. `midasi-w` being Roboto under a private name is evidence they do repackage, so "Roboto" here means "the files they serve", not necessarily "the Google font".


---

## 7. What transfers, and what does not

### 7.1 Transfers directly
- Light, near-white surface with brown ink (§3.2). Decisive.
- Action green at hue ~87°, used for affirmative actions only.
- Orange-up / blue-down delta semantics.
- Preview-before-commit as the interaction spine.
- Two-window desktop layout; persistent right rail.
- Card-shaped turn indicator rather than a progress bar.
- Six-column stat band with grade badge, value, and cap.
- Collapsible phase grouping for the turn timeline.
- Ribbon/capsule headers with lattice bleed; arrow-cap and notched silhouettes; scalloped gain bubbles.

### 7.2 Does not transfer, and why
- **3D rendered icons.** We cannot ship Cygames' art. The substitute must be flat glyphs drawn in the same *weight and softness* family, not a hairline icon set.
- **Per-trainee accent tinting.** Measured directly: Oguri Cap's HUD is green (`194819`, `202142`), Mejiro McQueen's is blue (`230755`). The client tints the HUD to the character. Our schema has no colour on `umamusume` and adding one is a schema change with no PRD requirement behind it, so the app keeps **one fixed accent** and reserves per-stat colour for the five disciplines. Flagged as an open question in DESIGN.md.
- **Photographic backgrounds and character portraits.** PRD §6.13 rules out trainee image uploads. The scene layer becomes a flat faceted field.
- **Celebratory italic display type** at race-result scale. Our tool has no race results (PRD §6.11); using it would be costume, not design. Reserved for the run-completion moment only.
- **Glassmorphism, dark chrome, neon trim.** Not present in the source material at all.

### 7.3 Where the game UI must be *simplified*, not copied
The client is built for a controller/touch rhythm with a character model carrying the emotional load. A logging tool is read-heavy and keyboard-driven. Three places the copy-the-game instinct would hurt us:
- The client's centred capsule headers waste horizontal space at desktop widths; ours keeps the shape but left-aligns.
- Circular discipline buttons are 90 px of chrome to say "Speed"; ours keeps the round glyph as the identifier but pairs it with a text label and a keyboard hint.
- The client's stat band shows one value at a time. Our run view needs the whole turn series, so the band becomes a header and the timeline becomes the body.

---

## 8. Unverified, stated plainly

- The exact font families used by the Global client. Screenshots give shape, not identity. Section 6 covers the *web* properties only.
- The precise hex of the grade-badge letters (S/A/B/C/D/E/F/G). The badges are ~28 px in the source frames and my block averages landed on the white interior rather than the letter fill. Values in §3.2 for grade colour are cluster peaks, which is weaker evidence than the point probes.
- Whether the JP and Global clients differ in UI colour at all, beyond the language. The corpus is entirely one client and I cannot tell which from the frames.
- Whether the discipline-banner colour is per-trainee, per-scenario, or per-season. Two data points (Oguri Cap green, Mejiro McQueen blue) are consistent with per-trainee but do not prove it; a third character would settle it and the corpus has more frames I did not open.
- The mood-pill colour for all five states. Only GREAT and GOOD were captured.
