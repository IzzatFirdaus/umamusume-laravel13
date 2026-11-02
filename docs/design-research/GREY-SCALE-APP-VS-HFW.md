# The grey scale: app against the HFW, token by token

**Question.** A reader looking at a page beside its wireframe says the cards look a different grey. Are the
two grey languages the same?

**Answer: no.** They share the *shape* — same `.card` rule, same 1px `rule-soft` border, same 14px/16px
padding, same radius family — but they differ on both surfaces that read as grey (`panel` and `page`) and on
every ink tier. Structure was ported; the palette was re-chosen.

Measured 2026-10-31 at HEAD against:

- HFW: `docs/design-research/frontend-v2-wireframes/assets/shell.css`, `:root` block, lines 8-28
- App: `resources/css/app.css`, light block, lines 136-169

Contrast figures are WCAG 2.x relative-luminance ratios computed from those hex values, not quoted from a
comment.

## The diff

| Token | HFW `shell.css` | App `app.css` | Same |
|---|---|---|---|
| `page` | `#F4F4FF` faint violet | `#F4F5F9` blue-grey | **no** |
| **`panel`** (the card fill) | **`#FBFBFD`** | **`#FFFFFF`** | **no** |
| `raised` | `#FFFFFF` | `#FFFFFF` | yes |
| `sunken` | `#EDEDF1` | `#EDEDF1` | yes |
| `idle` | `#D2D2DB` | `#E2E4F0` | **no** |
| `disabled` | `#D0D1D0` | `#D1D3E0` | **no** |
| `ink` | `#6A5641` warm brown | `#322A2F` warm near-black | **no** |
| `ink-strong` | `#482720` dark brown | `#000000` black | **no** |
| `ink-muted` | `#6A6A75` neutral grey | `#625D7D` violet-grey | **no** |
| `ink-faint` | `#988F87` warm grey | `#8C8A9A` cool violet-grey | **no** |
| `rule` | `#84828C` cool grey | `#8C8A9A` violet-grey | close, not equal |
| `rule-soft` | `#D9D8E2` | `#D7D8E0` | close, not equal |

**Only `raised` and `sunken` are byte-identical.** The app kept the *tier count* (page / panel / raised /
sunken, four steps) and re-chose two of the fills and the whole ink family.

## Why the cards look different, in numbers

| Measurement | HFW | App |
|---|---|---|
| `panel` luminance | **0.9660** (not white) | **1.0000** (exactly white) |
| `page → panel` separation | 1.057:1 | 1.089:1 |
| `panel → sunken` separation | 1.130:1 | 1.168:1 |
| `ink` on `panel` | **6.73:1** | **13.93:1** |
| `ink` on `page` | 6.37:1 | 12.78:1 |
| `ink-muted` on `panel` | 5.17:1 | 6.21:1 |
| `rule` on `page` (card boundary, needs 3:1) | 3.46:1 | **3.10:1** |
| `rule-soft` on `page` (quiet divider) | 1.29:1 | 1.30:1 |

Two things follow, and between them they explain the observation completely:

1. **The card's polarity inverted.** In the HFW the page is faintly tinted and the card is *almost* white
   (`#FBFBFD`, luminance 0.966) — a card that is a half-step duller than the paper's brightest point. In the
   app the card is **exactly white** on a bluer-grey page. The app's cards are therefore the brightest
   surface on the screen, while the HFW's are not. That is the "different grey" a reader sees, and it is a
   palette decision rather than a porting bug.

2. **The ink doubled in weight.** Body text sits at 6.73:1 on the HFW's card and 13.93:1 on the app's — more
   than twice the contrast, because the HFW's ink is warm brown (`#6A5641`) and the app's is a near-black
   with a violet-grey muted tier. On a white card that reads harder and cooler; on the HFW's off-white card,
   brown ink reads softer and warmer. Surface and ink moved together, which is why the difference is visible
   at a glance even though the border and radius are identical.

## What this is not

- **Not a contrast regression.** Every app pair that carries text clears 4.5:1 with room; the app is the
  stricter of the two on text and the *looser* on one boundary: `rule on page` sits at 3.10:1 against the
  floor of 3:1, where the HFW is at 3.46:1. That is thin but passing, and `app.css:153-165` records the
  reasoning (the brand hairline `#D1D3E0` measured 1.41:1 and could not carry a card edge, so `rule` takes
  the brand's muted step instead).
- **Not an oversight in either direction.** Both blocks carry dated provenance comments: the HFW's
  `--brand-chrome` line records an owner signature on 2026-10-15, and the app's block records the 2026-10-24
  Umamusume-brand ruling and the HLF reference sampling. Two ratified palettes, deliberately different.
- **Not a `panel` typo.** The app's `#FFFFFF` panel is consistent with its own `raised`, which is also
  `#FFFFFF` — so the app has *three* light tiers collapsing to white (panel, raised, and the lightest
  surface), where the HFW keeps panel one step below raised.

## If parity on this axis is wanted

Three changes, in ascending cost, none of them mine to take (both palettes are owner-ratified):

1. **`panel` → `#FBFBFD`** (one line). Restores the card's half-step and makes `panel`/`raised` distinct
   again, which is the HFW's shape. Risk: every card gets very slightly duller, and the light theme's
   `panel → sunken` separation drops from 1.168:1 to about 1.13:1.
2. **`page` → `#F4F4FF`** (one line). Restores the violet tint, which is what makes the HFW's white card
   read as *lifted* rather than *brightest*. Risk: the app's `rule` at 3.10:1 is tuned against the current
   page; moving the page changes that ratio and would need re-measuring against the 3:1 floor.
3. **The ink family** (`ink`, `ink-strong`) — the largest visual difference and the largest blast radius:
   every screen, both other themes, and every contrast figure recorded in `app.css`. Not a parity fix; a
   re-theme, and it should be its own slice with its own re-measurement.

**Recommendation:** if the goal is that a screen and its wireframe read alike, take (1) and (2) together in
one small slice and re-measure the four boundary ratios afterwards. Leave (3) alone unless the owner wants
the app to *be* the HFW's palette, because the current ink is the validated, stricter side of the pair.

## Scope of this report

- Compared the **default light theme only**. The app has `dark` and `eva01` overrides at `app.css:480` and
  `:614`; the HFW carries its own dark block at `shell.css:70`. A dark-theme comparison is a separate read.
- Compared **declared token values**, not rendered pixels. `bg-page` is consumed through the Tailwind theme
  (`AppLayout.vue:344`), so the compiled utility and the token agree, but a screenshot diff would catch any
  surface painted by a literal rather than a token — not attempted here.
- Did not diff the semantic families (green, status, mood, pick, goal, brand) beyond noting that the app's
  `--color-status-positive` is `#5D8F03` where the HFW's is `#BBDD11`. That is a different question from the
  grey language and deserves its own pass.
