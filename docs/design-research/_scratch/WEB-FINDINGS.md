# Official web properties — measured findings

Companion to `CLIENT-FINDINGS.md`. Scope: Cygames' **web** properties only. No production code.

## Method

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

## Global site (`umamusume.com`)

Content from Kuroco (`en-portal.g.kuroco-img.app`) and
`assets-webview-umamusume-en.akamaized.net`; shared chrome assets from `parts.umamusume.com`.

### Typography

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

### Colour tokens — exact measured `:root` custom properties

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

### Chrome / material

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

### Layout

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

### Motifs (what recurs)

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

### Motion (from `transition` values)

- Reveal wipe: `clip-path: inset(0 100% -20% 0)` → `inset(0)` under `.loaded .is-show`,
  `clip-path .4s cubic-bezier(.165,.84,.44,1) .3s`. Panel entrance: `translateY(80px)` + `opacity:0` →
  identity, `transform .7s cubic-bezier(.19,1,.22,1), opacity .7s cubic-bezier(.165,.84,.44,1)`.
- Character grid stagger: same clip wipe, `cubic-bezier(.25,.46,.45,.94)`, delays `.15 .20 .25 … .85s`
  (**50ms per index**, 15 slots).
- Card hover: `transform .3s cubic-bezier(.175,.885,.32,1.275)` (back-out overshoot). Tab/button hover:
  `transform: scale(.95)`, `.5s cubic-bezier(.19,1,.22,1)` — **press-in, no color flip**. Play Now color
  swap: `background-color .3s, color .3s cubic-bezier(.19,1,.22,1)`.
- Four curves total, dominated by `cubic-bezier(.19,1,.22,1)`.

### News list — dates and tags as actually built

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

## JP site (`umamusume.jp`)

Nuxt (`/_nuxt/…`, `data-v-*` scoped attributes), content from microCMS
(`images.microcms-assets.io`) + `prd-info-umamusume.akamaized.net`.

### Typography

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

### Colour tokens — exact measured `:root`

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

### Chrome / material

- Card: bg `#fff`, **`border-radius: 9px`**, `padding: 11.25px 11.25px 7.5px`,
  `filter: drop-shadow(rgba(0,0,0,.2) 0 2.25px 3.75px)` — tighter, less spread than EN.
  `.btn-movie` / `.btn-viewmore`: `border-radius: 3px`. Carousel arrows: 64×64, `radius 50%`, transparent.
- `backdrop-filter: none` everywhere sampled; `box-shadow: none`; `border: 0px`.
- `.new-icon` badge: 44×45 `<img src="/_nuxt/images/icon_news-*.png">` at the card's top-left.
- `body`/`html` bg `rgb(255,255,255)` with **no background image** — unlike EN's fixed pattern layer.
- **JP spacing is a 3.75px grid** (15px ÷ 4): 3.75 · 7.5 · 11.25 · 18.75 · 22.5 · 37.5 · 46.5 · 94.5.
  EN uses vw fractions of 1440. Same visual result, different unit philosophy.

### Layout

- `scrollWidth` 1440, `clientWidth` 1425, no overflow, `scrollHeight` 3350 on `/`. `.index__width`
  container **1251px**, `margin: 90px 94.5px 0`, `max-width: none`.
- Home is a **two-column asymmetric composition**, not full-width bands: `top-news` 576×667 @ x=87,y=999 ·
  `top-about` 605×305 @ x=734,y=999 · `top-contents` 605×305 @ x=734,y=1360 · `top-character` 1251×560 @
  y=1755 · `top-special` 473×134 @ x=87,y=2405 · `top-goods` 473×134 @ x=642,y=2405. Footer 661px.
- News panel is a **pink rounded container** holding 5 rows with a centered pink "View more" pill.
  Header 49px, transparent, `translateY(-48.75px)` when hidden on scroll.
- Body text 1479 chars on `/` — the page is almost entirely bitmaps (74 `<img>`).

### Motifs

Same parallelogram tabs, ribbon `clip-path` notch and `teitetsu` bullet emblem
(`class="nuxt-icon--fill teitetsu"`, 19.5px). **JP double-chevron differs:** inline
`viewBox="0 0 31.78 48.19"`, two *identical* chevron paths side by side, rather than EN's second-at-50%.
**JP-specific:** section tabs are **two-part** — a Latin word plus a smaller, darker parallelogram with
the Japanese gloss (`News` + `新着情報`, `About` + `ウマ娘について`, `Contents` + `コンテンツ`,
`Character` + `キャラクター`). Sparkle-texture pseudo-element overlays were **not observed** on JP home
buttons; JP uses flat fills.

## Divergences (EN vs JP)

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

## What transfers to an in-app tool — and what does not

### Transfer (cheap, distinctive, no assets required)

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

### Do not transfer

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

## Measured values worth turning into tokens

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

## Unknown / could not determine

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

## Screenshots

In `./web/`, copied from `C:\Users\exatf\.playwright-mcp\`, viewport 1440×900, `scale: "css"`:

- `en-home-hero.png` — EN `/` KV + fixed blue header, angled Play Now tab
- `en-home-news-section.png` — EN `/` News panel: pink container, skewed tab, card, "View more", diagonal band cut
- `en-home-characters-bento.png` — EN `/` character mosaic + Media tab
- `en-news-listing.png` — EN `/news/`: title parallelogram, 3 filter pills, white cards, green Game pill + date
- `en-characters-grid.png` — EN `/characters/`: 7-col grid, per-record accent, condensed name/VA labels, SVG underline bar
- `jp-home-hero.png` — JP `/` wordmark nav + KV
- `jp-home-news-and-contents.png` — JP `/` two-column: pink news panel with `[追記]` ribbon, two-part bilingual tabs
