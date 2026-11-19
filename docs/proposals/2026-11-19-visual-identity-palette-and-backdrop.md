# Visual identity: Azure Daybreak / Midnight Circuit palette and the backdrop layer

**Status:** proposal, not a slice. Nothing here authorises a commit; the owner names pathspecs.
**Date opened:** 2026-11-19
**Source:** owner paste, 2026-11-18/19: two palettes (Azure Daybreak, Midnight Circuit), the six-row
recommended-use tables, the ready-to-use CSS-variable block, the two image-generation prompts for the
light and dark backdrops, and the "suggested implementation" paragraph for translucent surfaces.
**Register reference:** none. This proposal files no `KI-nn`; the two defects it finds are named in §7
and would be filed only if the owner rules them in scope.
**Audit reference:** none. The relevant audit rows are already settled and are cited, not re-run.
**Ownership:** this proposal recommends; the owner rules. Slice ordering stays with
docs/roadmap/MASTER-IMPLEMENTATION-ROADMAP-2026-11-18.md §S0.6 (palette, gated on D-13) and
`docs/roadmap/UI-UX-IMPLEMENTATION-ROADMAP-2026-11-18.md` §12/§5.6 (which delegates palette work to
S0.6 and adds none of its own). Neither roadmap file is edited by this pass.

---

## 1. Problem

The owner supplied a complete visual identity: two named palettes, role-by-role hexes, a CSS-variable
block described as "ready to use", two backdrop prompts, and a translucency recipe. The tree cannot
absorb it as written, for four measured reasons, and the roadmap's own shovel-ready slice (S0.6) is
subtly wrong about what applying it means.

1. **The variable block names tokens that do not exist.** The paste writes `--primary`, `--background`,
   `--surface`, `--accent`, `--secondary`, `--highlight`, `--text-primary`, `--text-secondary`,
   `--border`. The tree declares 102 `--color-*` names in one `@theme static` block and none of those
   nine. Pasting the block produces nine dead custom properties and zero visual change.
2. **Two of the paste's light values fail the gates the tree already enforces.** Its `--border` #C8DDF2
   measures 1.18:1 on its own `--background` #DDEEFF against a 3.0:1 card-boundary requirement, and its
   `--primary` #347DFF carries white text at 3.80:1 against a 4.5:1 floor.
3. **S0.6's stated deliverable does not match the paste, and one of its stated risks is refuted.** S0.6
   lists `--color-primary`, `--color-accent`, `--color-secondary`, `--color-highlight` as tokens to set;
   none exist. It also warns that "Azure Daybreak page #DDEEFF with ink #6A5641 measures about 3.7:1
   (inferred), under 4.5:1". Measured, that pair is **5.88:1** and passes. The binding constraints are
   somewhere else entirely (§2, M-9 to M-12).
4. **The backdrop layer has no home yet and cannot be added as an arbitrary utility.** There is no
   background-image declaration anywhere in the app outside two decorative gradients in
   `resources/css/app.css`, `tools/ui-framework-check.mjs` fails a 210th arbitrary value against a
   209 baseline, and G-19 forbids a `dark:` variant as the switch. So the layer must be one
   token-driven utility in the stylesheet, not a class list at a call site.

**Final outcome wanted:** a ruled palette that keeps the owner's brand anchors, passes every existing
gate without weakening one, and a backdrop layer that is one declared utility plus one asset per theme.

---

## 2. Evidence

Baseline commit **`fec658f8`** (`fec658f83d1f3ef594285f3284719b3ab730c73f`), branch `master`.
Working tree at measurement: 11 modified, 24 untracked, 6 stashes, nothing staged.
`RENDERED_EVIDENCE = TRUE` (`node -e` launched `playwright.chromium`, set `background:#DDEEFF`, read
back `rgb(221, 238, 255)`). All ratios below are computed from WCAG 2.x relative luminance, the same
formula `tests/Feature/DesignTokensTest.php:608-618` uses.

| # | Fact | Value | How |
|---|---|---|---|
| M-1 | Colour tokens declared in `@theme static` | **102** unique `--color-*` | `grep -oE '^\s*--color-[a-z0-9-]+:' resources/css/app.css \| sort -u \| wc -l`; matches the pin at `DesignTokensTest.php:282` and `:673` |
| M-2 | Dark block declares | 79, inherits 23 from light | brace-scoped regex over `html[data-theme='dark']` |
| M-3 | eva01 block declares | 70, inherits 32 from light | same |
| M-4 | Paste's nine variable names (`--primary`, `--background`, `--surface`, `--accent`, `--secondary`, `--highlight`, `--text-primary`, `--text-secondary`, `--border`) | **0 of 9 exist** | set-difference against M-1 |
| M-5 | G-18 contract in the test | 48 text/boundary/hairline pairs + 9 grade badges = **57 rows**, thresholds 4.5 / 3.0 / 1.2 | `DesignTokensTest.php:419-523`, `:525+`, `:783-786` |
| M-6 | Surface-step floors and boundary floor | light 1.05, dark 1.25, `rule on page` 3.0 (eva01 exempt) | `DesignTokensTest.php:960-1001` |
| M-7 | Light page today | `--color-page: #F4F4FF`, panel #FBFBFD, raised #FFFFFF, sunken #EDEDF1, ink #6A5641, rule #8C8A9A | `app.css:144-175` |
| M-8 | Dark page today | `--color-page: #09090B`, panel #2A2A2A, raised #333333, ink #FCFCFC, rule #666666 | `app.css:506-515` |
| M-9 | Paste `--border` #C8DDF2 on paste `--background` #DDEEFF | **1.18:1** (needs 3.0) | computed; also 1.39 on #FFFFFF |
| M-10 | Paste `--primary` #347DFF with white ink | **3.80:1** (needs 4.5) | computed |
| M-11 | Paste `--primary` #347DFF as link text on #DDEEFF | **3.21:1** (needs 4.5) | computed |
| M-12 | S0.6's risk figure: ink #6A5641 on #DDEEFF | **5.88:1**, passes | computed; S0.6:284 says "about 3.7:1 (inferred)" |
| M-13 | Paste `--text-secondary` #647B98 on #DDEEFF | **3.68:1**, fails as text | computed; 4.35 on white |
| M-14 | Paste `--highlight` #FF65B8 with white | **2.69:1** fails; with #19365C **4.53:1** passes | computed |
| M-15 | Paste dark `--surface` #101F49 on `--background` #050A1B | **1.23:1**, under the 1.25 dark floor | computed |
| M-16 | Paste dark `--secondary` #283B70 on #050A1B | **1.83:1** as a boundary (needs 3.0); 1.48 on the surface | computed |
| M-17 | Paste dark `--highlight` #F02FA9 with white | **3.69:1** fails; with ink #EAF2FF **3.28:1** fails; with page ink #050A1B **5.33:1** passes | computed |
| M-18 | Paste dark `--primary` #164BFF with white | **6.03:1**, passes as given | computed |
| M-19 | `dark:` utilities in live source | **0**; 3 grep hits, all inside docblock comments | `grep -rn 'dark:' resources` |
| M-20 | Arbitrary-value baseline | **209**; a 210th fails the ratchet | `node -e "require('./tools/ui-lint-baseline.json').count"`; `tools/ui-framework-check.mjs:196` |
| M-21 | Existing `background-image` declarations in the app | **2**, both decorative (argyle lattice, turn-chip holes), both inside `@utility` blocks in `app.css` | `app.css:797`, `:822` |
| M-22 | `bg-page` consumers | 9 files: `app.blade.php`, three error blades, `AppLayout.vue`, `RaceCard.vue`, `StatSummary.vue`, `StickyFooter.vue`, `useRole.ts` | `grep -rl` |
| M-23 | Layouts | 3; every page reaches `AppLayout` (directly, or through `CareerLayout` / `SetupLayout`, which both wrap it) | `resources/js/layouts/` |
| M-24 | Backdrop source images at repo root | 19 files, **18.3 MB**, all untracked | `git status --porcelain -uall`; PIL inventory |
| M-25 | Those files' real formats | bg-light.png, bg-dark.png, bg-eva01.png and their `_chatgpt` twins are **JPEG** data with a `.png` extension | PIL `Image.open(...).format` |
| M-26 | Candidates against the paste's own 1920×1080 minimum | `*_uma_chatgpt` are 1672×941 (**below** the stated minimum); `*_qwen` are 2752×1536; the flat bg-*.png are 1024×576 | PIL |
| M-27 | Light-candidate worst-case ink contrast, whole frame | `bg-light_uma_chatgpt`: p1 patch rgb(51,143,253), p99 rgb(241,248,254). Ink #19365C over a page-colour veil: 0.88 → 9.20, 0.80 → 8.52, 0.72 → 7.88, 0.60 → 6.99 | numpy per-pixel luminance across the full image, not the calm zone only |
| M-28 | Same for `bg-light_qwen` | p1 rgb(248,246,252), p99 rgb(251,249,255): 10.42 to 10.71 at every veil tested. Effectively a wash, not a scene | same |
| M-29 | Dark-candidate worst case | `bg-dark_uma_chatgpt` p99 rgb(52,204,254) (floodlights): ink at 0.88 → 14.47, 0.80 → 12.03, 0.72 → 9.73, 0.60 → 6.95. `bg-dark_qwen` is flat and stays above 17.3 at every veil | same |
| M-30 | Recompression headroom | 1920×1080 WebP q72: 99.3 KB light / 106.2 KB dark. 2560×1440: 138.2 / 147.1 KB. From 1.75 MB / 1.82 MB PNG | PIL, `method=6` |
| M-31 | Brand assets | resources/img/app-logo.png and public/favicon.png are both 1254×1254, 1.39 MB / 1.35 MB, **untracked** | PIL, `git status` |
| M-32 | Logo render size | `size-5.5` = 22 px box in the rail head | `AppLayout.vue:380` |
| M-33 | Working-tree edit to the shell | the rail mark went from a lettered `TD` span to `<img :src="appLogo" alt="Trainer Desk" ... aria-hidden="true">`, and `app.blade.php`'s icon link moved from `/favicon.svg` (**tracked**) to `/favicon.png` (**untracked**) | `git diff HEAD -- resources/js/layouts/AppLayout.vue resources/views/app.blade.php` |

Two consequences fall straight out of M-24/M-31/M-33 and are stated here rather than buried in §7: a
clean clone currently 404s its own favicon, and the repo root is carrying 18.3 MB of untracked image
drafts, which AGENTS.md §12 (containment) and §11 (generated/protected) both speak to.

---

## 3. What current code does

Read-only summary; no change proposed in this section.

- **One token vocabulary, three themes.** `@theme static` (`app.css:24-430`) holds the light values and
  is the only place colour enters Tailwind. `html[data-theme='dark']` (`:438-635`) overrides 79 of the
  102; `html[data-theme='eva01']` (`:650-750`) overrides 70. A component never branches on theme: the
  variables flip and the same class list serves all three (comments at `:14-18`, gate G-19).
- **The theme reaches first paint server-side.** `AppServiceProvider` composes `$theme` from
  `Preference::resolvedTheme()`, `app.blade.php:7` prints `data-theme`, and `useTheme.ts` mirrors the
  Inertia prop onto the same attribute so a save on `/preferences` flips without a reload. A null theme
  means "follow the OS" and resolves through `prefers-color-scheme` in both places.
- **Roles are named, not numbered.** The action fill is `--color-primary-action`, an *alias* of
  `--color-brand-chrome` (`app.css:414`), and the dark block redefines only the alias target so both
  roles follow. `--color-brand-accent` is the CTA chip, `--color-ring` the focus ring, `--color-chrome`
  the lime capsule. There is no generic `--color-primary` / `--color-surface` layer, by design: DESIGN.md
  §1.2 (colour has three roles) and §2.1's per-role table are the authority.
- **The gates the palette must satisfy.** `DesignTokensTest` pins the token count twice (M-1), resolves
  every declared token in every theme and fails on empty (D-288), measures 57 contract rows per theme
  against the three-way threshold model (M-5), and separately measures the card surface step and the
  `rule on page` boundary (M-6). `tools/ui-framework-check.mjs` separately fails a 210th arbitrary
  value (M-20). DESIGN.md §1.4 bans "pastel anime gradients" and glassmorphism as *marketing*
  decoration; the 2026-10-15 amendment keeps the ban and authorises the client's own brand hues.
- **No image layer exists.** The only background images in the app are two decorative gradients inside
  `@utility` blocks (M-21); `bg-page` is a flat fill on `<body>` and on 8 further call sites (M-22).

---

## 4. Recommendation

One recommendation per owner decision in §7. Each cites the rule it obeys.

### 4.1 Adopt both palettes, re-spelled onto the existing 102-name vocabulary (D-13a)

The paste's six role names per theme are the product requirement. The *variable names* are not: they
would be nine new dead properties (M-4). Map every paste role onto the role the tree already owns, and
where the tree has no equivalent, mint it once with a G-18 row:

| Paste role | Tree token | Note |
|---|---|---|
| Primary | `--color-brand-chrome` / `--color-primary-action` | alias, per `app.css:414`; one hex, both roles |
| Background | `--color-page` | |
| Surface | `--color-panel` (cards) and `--color-raised` (data cells) | the tree splits these; both were #FFFFFF in the paste's light column |
| Accent (cyan) | new `--color-accent-sky` | no current token holds a decorative sky fill |
| Secondary (mint) | `--color-green` / `--color-chrome` | already the status-positive role; keeping the pair avoids a status pill and a primary button sharing a fill (DESIGN.md §2.1 guard b) |
| Highlight (pink) | `--color-brand-accent` | already the CTA chip colour |
| Text primary / secondary | `--color-ink` / `--color-ink-muted` (+ `ink-strong`, `ink-faint` for the two tiers the paste omits) | |
| Border | `--color-rule` (boundary) and `--color-rule-soft` (in-card divider) | the tree splits them for exactly the 1.18:1 reason in M-9 |

This obeys DESIGN.md §2.1 (one token vocabulary, per-role table is the contract) and G-19 (no `dark:`).

### 4.2 Keep the brand anchors, step the four values that fail, and record each step (D-13b)

The owner's own framing ("keep Azure Blue and Electric Blue shared brand anchors, cyan and pink acting
as bridge") is achievable, but not at the given hexes. Starting draft, every value measured:

**Light - Azure Daybreak**

| Token | Value | From the paste | Measured |
|---|---|---|---|
| `--color-page` | #DDEEFF | kept (Ice Blue) | ink 10.30, muted 5.45 on it |
| `--color-panel` / `--color-raised` | #FFFFFF | kept (Pure White) | step to page 1.18 (floor 1.05) |
| `--color-sunken` | #CFE3F6 | new; the paste has no recess | step to panel 1.31; ink-muted 4.91 |
| `--color-idle` | #BBD5EE | new | ink 8.04 |
| `--color-ink` | #19365C | kept (paste's `--text-primary`) | 10.30 on page, 12.18 on white |
| `--color-ink-strong` | #0E2745 | new; paste has no strong tier | 15.06 on white |
| `--color-ink-muted` | #48607F | **stepped** from #647B98 (M-13) | 5.45 page / 6.45 panel / 4.91 sunken |
| `--color-ink-faint` | #8FA6BE | new | non-text only, 2.51 on white |
| `--color-rule` | #5E88B5 | **stepped** from #C8DDF2 (M-9) | 3.13 page, 3.71 white |
| `--color-rule-soft` | #C8DDF2 | kept, hairline role only | never a card edge |
| `--color-brand-chrome` | #2455C9 | **stepped** from #347DFF (M-10/M-11) | white on fill 6.53; as text 5.52 page / 4.96 sunken |
| `--color-on-brand-chrome` | #FFFFFF | kept | |
| `--color-brand-accent` | #FF65B8 | kept (Racing Pink) | ink #19365C on it 4.53 |
| `--color-green` / `--color-chrome` | #B9F0DE | kept (Mint Green) | ink 9.62 |
| `--color-accent-sky` | #9DDCFF | kept (Sky Cyan) | ink 8.19 |

**Dark - Midnight Circuit**

| Token | Value | From the paste | Measured |
|---|---|---|---|
| `--color-page` | #050A1B | kept (Deep Navy) | ink 17.49 |
| `--color-panel` | #12224F | **stepped** from #101F49 (M-15) | step to page 1.28 (floor 1.25); ink-muted 7.36 |
| `--color-raised` | #16295C | new | ink 12.38 |
| `--color-sunken` | #02060F | new | ink-muted 9.71 |
| `--color-idle` | #1A2E63 | new | |
| `--color-ink` | #EAF2FF | kept (paste's `--text-primary`) | |
| `--color-ink-strong` | #FFFFFF | new | |
| `--color-ink-muted` | #9FB4DE | kept (paste's `--text-secondary`) | 9.44 page / 7.65 panel (M-15's surface) |
| `--color-ink-faint` | #5A78BC | new | |
| `--color-rule` | #666666 | **kept from the current dark block**, not the paste's #283B70 (M-16) | 3.43 page |
| `--color-rule-soft` | #283B70 | the paste's Circuit Blue, demoted to divider | hairline only |
| `--color-brand-chrome` | #164BFF | kept (Electric Blue) | white on fill 6.03, passes as given (M-18) |
| `--color-on-brand-chrome` | #FFFFFF | kept | |
| `--color-brand-accent` | #F02FA9 | kept (Hot Magenta) as a **fill**, carrying page ink #050A1B at 5.33 (M-17) | white/mint ink banned on it |
| `--color-brand-accent-ink` | #FF5CB8 | **stepped** for the pink-as-text tier | 5.69 on panel, 7.01 on page |
| `--color-ring` | #22C8FF | kept (Neon Cyan) as fill/ring, never as text | 10.11 as fill |

Two hard rules fall out and belong in DESIGN.md when this lands: **pink never carries white text in
either theme** (2.69 light, 3.69 dark), and **neon cyan never carries ink on top of it** (ink #EAF2FF
on #22C8FF is 1.73:1); it is a fill, a ring and a rail only.

Three G-18 rows are owed and do not exist yet: `brand-accent-ink on sunken` (4.96 measured, passes),
`rule-soft` pairs (hairline 1.2, currently unmeasured), and a new composite row for §4.5's backdrop.
Adding rows is permitted; removing a failing row is not (`DesignTokensTest.php:494-497` states the
prohibition).

### 4.3 Add a `backdrop` utility, one declared image token per theme (D-13c)

One `@utility` in `resources/css/app.css`, next to the existing decorative ones (M-21), so no call site
carries an arbitrary value (M-20) and no component branches on theme (G-19):

```
--backdrop-image: none;                       /* light: @theme static */
html[data-theme='dark'] { --backdrop-image: url('/img/backdrop-night.webp'); }
html[data-theme='eva01'] { --backdrop-image: none; }

@utility app-backdrop {
    background-image:
        linear-gradient(color-mix(in srgb, var(--color-page) 78%, transparent),
                        color-mix(in srgb, var(--color-page) 78%, transparent)),
        var(--backdrop-image);
    background-attachment: fixed;
    background-size: cover;
    background-position: center;
}
```

The 78% veil is not a taste number: at 0.80 the worst patch of every candidate still carries ink at
8.52 (light uma scene) and 12.03 (dark uma scene); at 0.72 it is 7.88 / 9.73 (M-27, M-29). It also
means the *image* never has to be contrast-safe, only the *composite*, which is why the composite row
in §4.2 is the gate that matters. `color-mix()` shipped in every core browser during 2023, inside
AGENTS.md §13's Baseline 2024 rule. The token is deliberately *not* named `--color-*`, so M-1's 102
pin does not move; the consequence is that `DesignTokensTest`'s resolution sweep will not see it, so
the layer needs its own assertion (slice 3.3).

Preconditions the slice must also carry, each one line: `@media print { .app-backdrop { background-image: none } }`,
`@media (forced-colors: active)` the same, and `background-attachment: fixed` only at `min-width: 768px`
(it is a known mobile scroll-jank source). The element that takes the class is `<body>`'s existing
surface in `app.blade.php:25` and the three error blades, or `AppLayout.vue:353`'s root - pick one, not
both, or the veil stacks twice.

### 4.4 Fix the asset pipeline before shipping any of it (D-13d)

- **Formats**: export to WebP at 1920×1080 and 2560×1440 (M-30: 99-147 KB each). Two of the four
  candidates are below the paste's own 1920 minimum (M-26) and four are JPEG data wearing a `.png`
  name (M-25); both facts are invisible until something reads the header.
- **Logo and favicon**: 1254×1254 at 1.35-1.39 MB to render a 22 px box (M-31, M-32). Ship 64 px and
  128 px derivatives. Keep the tracked `public/favicon.svg` (114 KB, already referenced by every
  historical build) and add a small PNG only if the SVG loses the mark at 16 px.
- **The clean-clone 404** (M-33): `app.blade.php:12` now points at an untracked file. Either the file
  is added to the commit or the link goes back to `/favicon.svg`.
- **Containment**: 18.3 MB of drafts sit at the repo root, which AGENTS.md §12 forbids for artifacts;
  the chosen two move to resources/img/ (the directory the Vite manifest already includes, per the
  generated public/build/manifest.json) and the rest leave the tree by owner instruction.
- **The rail mark**: the working-tree `<img>` carries `alt="Trainer Desk"` *and* `aria-hidden="true"`
  while the word "TRAINER DESK" prints beside it. One of the two has to go; with the word present the
  image is decorative and takes `alt=""`. That edit belongs to whoever owns the uncommitted hunk, not
  to this slice.

### 4.5 Framework adherence

- **Justified.** DESIGN.md §1.2's three-role model (brand chrome, brand accent, semantic state) is what
  makes a "primary blue plus pink highlight" identity expressible without a fourth role; §2.1's
  light-first ruling is untouched (only the light palette's *values* move). G-18/G-19 are satisfied by
  measurement and by having no theme fork.
- **Constrained.** §1.4's ban on marketing gradients and glassmorphism survives: the backdrop is a
  photograph behind a flat page-colour veil, the chrome stays "a flat fill plus a thin accent stripe",
  and the veil is one `linear-gradient` of a single colour, which is a scrim and not an identity
  gradient. §2.3's "shadows stay absent" is untouched. No new dependency: `color-mix()`, `background-size`
  and `@utility` are all already in use.
- **Accepted limitation.** A full-bleed image behind a data-dense table is a readability tax that no
  gate fully prices. The measured answer is the veil floor (§4.3) plus the composite G-18 row; the
  unmeasured remainder is perceptual, and the honest statement is that a page-level screenshot review
  after the slice is the only check for it.
- **Not applicable.** Nothing here touches fetching, provenance, schema, `/api/v1`, auth (none by
  design) or the lore gate. No new package, no new root document.

---

## 5. Rejected alternatives

| Option | Why rejected |
|---|---|
| **Paste the CSS-variable block as given** | Nine of its nine names are dead in this tree (M-4); the two hexes it most depends on fail G-18 (M-9, M-10). It would ship a no-op diff that looks like a theme change. |
| **Rename the tree's tokens to the paste's generic names** | 102 names, 48 contract rows, 9 grade rows, 79 dark overrides and 70 eva01 overrides all key on the current names; `--color-primary` also collides conceptually with the action alias. A rename of that size is a re-platform, not a palette. |
| **Add a fourth theme instead of retuning light and dark** | The paste names Azure Daybreak and Midnight Circuit as *the* light and dark modes, and `Preference` resolves exactly three stored values plus OS-follow; a fourth theme doubles every future palette measurement and leaves the two the owner actually uses unchanged. |
| **Keep the current palettes and treat the paste as aspirational** | The master roadmap already carries S0.6 as owner-gated work with a named decision (D-13); declining would need its own written ruling, and the measured deltas here (rule 2.85, chrome 3.80) are real failures that improve when retuned. |
| **Ship the backdrops with no veil, relying on the "UI safe area" composition** | The prompt's calm zone is a bet on the generator, not a guarantee. Measured, the same image's p99 patch is 24× brighter than its calm zone (M-27); a card that lands on the wrong patch would fail silently, and no gate would see it. |
| **`bg-[url('/img/backdrop.webp')]` at the layout call site** | 210th arbitrary value (M-20) fails the ratchet, and a `dark:` twin is banned outright (M-19). |
| **Generate new backdrops now** | Six existing candidates cover the compositions the prompts describe; §4.4's recompression is the measured blocker, not the artwork. Regeneration is the upgrade path if the owner rejects all six, not the first move. |

---

## 6. Would not change

- **eva01.** Standalone palette, owner-requested 2026-10-24, inherits 32 tokens from the light block
  (M-3). Contract: it is not touched, and the `--backdrop-image: none` line above only keeps it from
  inheriting a photo it was never designed under.
- **The theme wiring.** Server compose, first-paint attribute, `useTheme()` hydration, OS-follow,
  `/preferences` radio tiles: all measured green and none of it needs to move for a palette swap.
- **G-18 thresholds, the surface-step floors, the 102 pin and the arbitrary-value baseline.** Floors
  are satisfied, never updated. If a chosen hex cannot clear a row, the hex moves (as §4.2 does four
  times) - the row does not.
- **Layout, spacing, typography, radius, motion, shadows.** The ask is colour, image and brand assets.
- **The two roadmap files.** They stay as peers left them; this proposal supplies the D-13 evidence
  they point at rather than editing their slice text.

---

## 7. Owner decisions

| # | Decision | Recommendation | Evidence |
|---|---|---|---|
| D-13a | Adopt both palettes, re-spelled onto the existing role tokens | Yes | M-4; §4.1 mapping |
| D-13b | Approve the four stepped light values (`rule`, `ink-muted`, `brand-chrome`, plus `sunken` as new) and the four dark ones (`panel`, `rule` kept from today, `rule-soft`, `brand-accent-ink`) | Yes, as the measured starting draft; invite a different hue below the same floors | M-9 to M-18 |
| D-13c | Backdrop layer as a token-driven utility with a 78% page-colour veil | Yes, for the light theme; for dark, **optional** - the dark scene's brightest patch is 4x safer but the scene is also busier | M-27, M-29 |
| D-13d | Choose one light and one dark candidate, and authorise recompression to WebP + resizing the logo/favicon | Yes; `bg-light_uma_chatgpt` (needs a 1920 master) and `bg-dark_qwen` are the measured-safe pair, `bg-dark_uma_chatgpt` is the atmospheric one | M-26 to M-30 |
| D-13e | Authorise the peer-owned shell hunks (`app.blade.php` favicon path, `AppLayout.vue` rail `<img>` + `alt`) to be finished by their owner, not by this slice | Yes | M-33 |
| D-13f | Mint `--color-accent-sky` at all, or keep cyan to backdrops and decoration only? | Mint it; it gives the charts and the "bridge" role the paste describes one home | §4.1 |

Two defects worth a `KI-nn` if the owner wants them tracked rather than fixed in passing: the
clean-clone favicon 404 (M-33) and the inert `alt` on an `aria-hidden` rail mark (M-33, §4.4).

---

## 8. Risk and reversibility

| Risk | Blast radius | Mitigation / reversal |
|---|---|---|
| Palette swap changes every screen | 62 pages, 119 components, 3 layouts, 4 Blade shells | It is one file (`resources/css/app.css`) plus the DESIGN.md record. Revert is `git revert` of that commit; no data, no schema, no route |
| A stepped hex fails a G-18 row on a real browser | the 57-row contract + surface-step test, per theme | The four steps in §4.2 were computed with the test's own formula; run `DesignTokensTest` before the browser pass, then the browser pass. A failure is an ink ruling, never a silent default |
| Backdrop hurts readability on a dense screen | every `bg-page` consumer (M-22); worst on tables and the error pages | The veil floor (7.88 worst case at 0.72) plus a new composite row; `@media print` and `forced-colors` opt out; removing the utility reverts it in one line |
| Asset weight | first paint, the one Core Web Vital a full-bleed image touches | 99-147 KB WebP vs 1.75 MB PNG (M-30); one image per theme, no preload before the palette opinion is ruled |
| Picking a candidate below 1920 | visible upscaling on a 1440p display | M-26 names which are below the floor; regenerate rather than upscale |
| The 209 ratchet | a call-site `bg-[url()]` fails the build gate | §4.3 puts the URL in the stylesheet, where the rule does not look |

---

## 9. Proposed slice shape

Not a commitment; shape only, so the owner can rule on boundaries rather than on a file list.

1. **Slice A - palette, CSS only.** `resources/css/app.css` light block, dark block, and the DESIGN.md
   §2.1 record (dated, preserving the superseded values the way the file already does). No component,
   no template, no image. Acceptance: `DesignTokensTest` 57 rows x 3 themes green, surface-step rows
   green, `npm run typecheck`, `npm run build`.
2. **Slice B - backdrop utility and two assets.** Adds `--backdrop-image` in each block, the
   `@utility app-backdrop`, one element owns the class, two WebP exports under resources/img/.
   Acceptance: as A, plus a new assertion that the token resolves per theme and that no `dark:` twin
   exists, plus a rendered check that a card over the busiest patch still clears 4.5:1.
3. **Slice C - brand assets.** Logo/favicon derivatives, the favicon path reconciled, the rail-mark
   `alt` decided by its owner, the root drafts removed by owner instruction.

A and B are one decision apart but should not be one commit: the palette must be ruled before the
image that sits behind it can be judged.

---

## 10. Does not do

No code in this pass. No slice, no dispatch, no commit, no staging. No new dependency. No schema. No
change to either roadmap file, to `DESIGN.md`, to `tests/Feature/DesignTokensTest.php`, or to any
peer-owned working-tree path. No image is generated, moved, deleted or renamed; every image statement
above is a read.

---

## 11. Change log

| Date | Change | Reason |
|---|---|---|
| 2026-11-19 | Opened at `fec658f8`; palettes measured against the tree's own G-18 formula; S0.6's "about 3.7:1 (inferred)" refuted at 5.88:1; four light and four dark values stepped with the ratio beside each; backdrop veil floor derived from whole-frame pixel statistics rather than the prompt's calm zone; asset and clean-clone findings recorded | Owner supplied a full visual identity (palettes, prompts, translucency recipe) and asked for a document under docs/plan, docs/plans or docs/proposals. The paste's variable block cannot be pasted into a 102-name vocabulary, and its two load-bearing hexes fail existing gates |
| 2026-11-19 | **Follow-up, after the first pass shipped: the light ramp was still reading white.** `rule on page` pinned `page` at L 0.800 (a 0.13 margin), so the backdrop band was 0.20 wide and only a near-white plate fit it. Darkening `rule` and the page together widens the band to 0.588..1.0; the ladder was then compressed so no light surface is pure white, and the plate re-generated inside the new floor | Judged on the rendered dashboard: `page` #DDEEFF with `panel`/`raised` #FFFFFF is near-white with white cards, so the theme's name was in the copy and not in the pixels. Fourteen further values are gate-forced steps, each re-measured; light renders 0 contrast failures at worst 4.73:1 |
| 2026-11-19 | **Third theme: eva01 became a real Unit-01 identity** (purple-dominant ramp + its own launch-bay plate). 8 of 18 pasted pairs failed G-18, all because a dark page needs lighter foregrounds; purple/secondary-purple/burgundy each split into a fill and a text step, `risk` took `#FF7581` because BO-004 prints it as text, and the old `rule on page` 2.70:1 exemption is retired (new `rule` measures 4.58:1) | Owner supplied the EVA-01 palette and a Unit-01 backdrop and asked for the theme. Rendered: all three themes 0 failures, eva01 1.92 -> 4.57 worst |
