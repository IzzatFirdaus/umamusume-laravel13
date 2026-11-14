# GP-08 Official-site + pre-alpha archive colour review

2026-11-14. Read-only review of `docs/archive/**` and the official portals; one file written (this
memo). All fetches are raw source; declared values outrank pixels throughout.

## Method, fetch log, limitations

Fetch method: Python `urllib` with a browser UA; raw HTML/CSS saved under
`.scratch-uma/grey-parity-2026-11-14/webfetch/`. Log (UTC+8):

| URL | Time | Status | Size |
|---|---|---|---|
| `umamusume.jp/` | 23:57:36 | 200 | 771,340 B |
| `umamusume.jp/character/` | 23:57:37 | 200 | 1,035,665 B |
| `umamusume.jp/character/specialweek` | 23:59:04 | 200 | 748,969 B |
| `umamusume.jp/character/goldship` | 23:59:06 | 200 | 748,837 B |
| `umamusume.jp/_nuxt/css/entry-3AQM60V0.css` (+4 more css) | 23:58:39-42 | 200 | ≤9,329 B each |
| `umamusume.com/` | 23:57:39 | 200 | 26,514 B |

No rendered captures were needed: source carried the answers (declared-over-pixel rule).

**Recon premise corrections (both verified, not copied):** (1) `umamusume.jp` is **Nuxt 3, not
theseed** (`/_nuxt/` bundles in page source; zero "theseed" markers anywhere) and its page source
carries **no dark-theme mechanism at all** — 0 `prefers-color-scheme` across 4 HTML pages
(83 inline style blocks), 5 stylesheets, and a full href sweep. The recon's `@theseed-dark-mode`
block (`#41444b`/`#e5a575`) is **not on this host**. (2) `umamusume.com` is a 26 KB JS shell
(title confirms Cygames EN portal) with **zero `--color-*` declarations** in source.

## Phase A inventory (87 tracked files; git `docs/archive/`)

| Group (count) | Type | Provenance class | Basis |
|---|---|---|---|
| `HLF_reference/` (15) + `dark_mode/` (25) | v0.build reference-site captures (2026-10-13/14) | **HLF reference, light+dark — highest-weight local dark source, but it depicts the *project's v0.build reference site* (Vercel v0-generated), neither the game nor the portal nor shipped app screens** | filenames `screencapture-ui-improvement-suggestions-v0-build-…`; ratified as palette source 2026-10-24 (`DESIGN.md:196-211`, which names both paths; `app.css:161` samples it by filename) |
| `mockups/` (29) | project's own pre-alpha mockups (2026-09-27) | **Pre-alpha design intent** | project-produced; already cited by `app.css:125-134` (page/ink sources) — decision-history, not game fidelity |
| `vibe_images/` (9) | re-uploads of mockups | Pre-alpha design intent (duplicate) | byte-identical to `mockups/` files (sha256 verified, 3 of 3 sampled) |
| `verification/` (4) | WS render verifications | Pre-alpha verification stills | tiny (24-186 KB), build-state evidence |
| `adr/` (5) | archived ADRs | n/a (records) | read-only history |

**Overlap:** `DESIGN.md:198` cites the old path `docs/design-research/HLF_reference/`, but the only
existing copy is `docs/archive/HLF_reference/` — the citation is stale and should point at the
archive path (documentation erratum candidate, not a colour finding).

## Phase B first-party declarations

**Global (jp root + all pages, inline):** `--color-text-base:#4d4d4d`, `--color-text-gray:#141414`,
`--color-gray:#d2d2d2`, `--color-blue:#2a5dfa`, `--color-green:#69c832`, `--color-yellow:#ffba00`,
`--color-orange:#ff9600`, `--color-error:red`, `--color-btn:#4d4d4d`; `body{background-color:#fff}`.
**No tier ramp declared** (no panel/raised/sunken tokens); surfaces are flat white.

**Per-character identity pairs** (mechanism: inline custom properties on each page's
`.site-wrapper`): Special Week `#EE6DCB`/`#FFDEF9`, Gold Ship `#DA3C57`/`#E9DA36`, +5 more from the
index (`#29BD70`/`#FFCE48`, `#3376D2`/`#FF99D0`, `#EA504A`/`#FFCD00`, `#444745`/`#33B839`,
`#3A7AD2`/`#ECE7E7`). Semantic/brand identity palette — **out of scope for the grey plan**.

**Dark theme: absent from the JP portal.** The single most-sought first-party dark evidence does
not exist on this host. Not "unfetched" — 4 pages, 83 style blocks, 5 stylesheets, all hrefs swept.

## Secondary-source leads

The recon's "2021 UI survey" (`Main #776906`, `Text Base #794016`, `Others #625d7d`) was **not
fetched** (no URL given in the brief) and stays unverified. Label: `secondary — low confidence,
2021`. Disagreement flags: its values appear in **no** first-party fetch; `#625D7D` matches the
app's current `--color-ink-muted` byte-for-byte, but **no first-party source declares it** — the
match is with a labelled secondary, not with the game. The app's own committed corpus already
contains `#625D7D` as a *measured* value (`_scratch/analysis/tokens/tokens.json:144`, muted anchor)
— that is the strongest local corroboration the value has, and it is project-measured, not
first-party-declared.

## Phase C answers

1. **Ink temperature.** First-party JP ink is **neutral-dark, not warm brown** (`#4d4d4d`/`#141414`
   on white). The warm-brown C1a set rests on the project's own ratified measurements (the
   `_scratch` corpus anchors + HFW), not on Cygames web declarations. The 2021 survey's `#794016`
   is the only game-client warm-ink claim and it is secondary. **Neither confirms nor refutes
   C1a**; it removes "Cygames ships brown ink on the web" as an argument.
2. **Surface polarity / tier ramp.** **No tier contract declared** — flat `#fff` body, no
   panel/raised/sunken. The app's four-tier governance is an **app invention** (governance choice),
   matching the HFW's shape but not derived from any first-party ramp.
3. **Dark mode.** First-party web dark: **absent** (premise failed). Local dark evidence exists and
   is ratified-adjacent: `HLF_reference/dark_mode/` (25 captures, 2026-10-13), already the recorded
   source of the app's dark `page/panel/raised/sunken` values (`DESIGN.md:196-211`).
   Evidence-sized: a ratified local reference exists; first-party web corroboration does not. Any
   dark slice remains a **scope expansion needing an owner ruling**.
4. **Contrast posture.** First-party band: `#4d4d4d` on white **8.45:1**, `#141414` on white
   **18.42:1** (computed from declared values). Between the HFW/corpus's ~6.5:1 and the app's
   ~13:1, closer to neither; the portal is not a contrast model for the app.
5. **Pre-alpha intent vs shipped state.** The mockups' warm-cream page + `#322A2F` ink are exactly
   what the 2026-10-25 synthesis adopted, then the same-day owner palette (`9fa70510`) re-pinned
   page to `#F4F5F9`; `bc102252` later moved page/panel to the HFW set. **Intent → ratified chain
   is continuous; no abandoned grey-language intent exists in the archive.**
6. **House style.** The app alone carries: the four-tier ramp with floor guards (1.05/1.25,
   `DesignTokensTest:903`), 41 G-18 contract pairs, a non-text `ink-faint` tier with a recorded
   prohibition (`app.css:145-151`), and provenance comments per token. Neither the game corpus nor
   the portal declares any of this. **This is the thing worth protecting.**

## Phase D classification + owner question(s)

**Classifies as: enables a named candidate + confirms the plan elsewhere.**

- **Enables:** a dark-mode reference review (`HLF_reference/dark_mode/`, 25 captures) is now
  *sized and named*: ratified-adjacent, game-independent, and the only dark evidence base that
  exists. Owner ruling required to open it as a scope expansion; this memo does not plan it.
- **Confirms:** C1 stays a taste ruling with no first-party game-web support in either direction
  (removing a would-be argument); C2 untouched; the four-tier governance stands as an app
  invention worth keeping; no ratified decision is re-opened.
- **Owner questions raised:** (a) open a dark-mode reference review on the HLF dark subtree? (b)
  should the C1a ruling be re-framed now that "the game does it too" is no longer available as a
  justification (the corpus measurement + HFW shape remain the entire case)? (c) fix the stale
  `DESIGN.md:198` HLF path citation (doc erratum, no colour change)?

## Coverage limitations

- The recon's theseed host was never identified (no URL given); its dark block may exist on a
  *different* host — unproven either way, reported as unverifiable rather than assumed.
- The 2021 survey was not fetchable from the brief (no URL); its three values remain unverified
  secondary claims.
- `umamusume.com`'s theme is JS-applied; if it ships runtime dark styling it is invisible to source
  fetch (26 KB shell). A rendered pass could close this; not run (source-first rule satisfied,
  render adds nothing the memo needs).
- Archive PNGs were not pixel-sampled — no declared values exist inside them, and the memo's
  comparisons needed only the declarations plus the corpus JSONs.
- Character `--color-main/sub` values sampled from 2 detail pages + 5 index pairs (7 of ~190);
  mechanism confirmed, coverage partial by design (semantic scope).

---

## Erratum 2026-11-14 (correction pass, item 1 — the provenance conflict this memo created)

This memo's Phase A note ("no drift: tokens match their recorded sources") and the stat plan's
"mockups, not HLF" line were in tension with 06/GP-08's HLF framing. Settled by value-by-value
diff: the current dark block (`app.css:484-493`) matches the mockups-charcoal synthesis comment
(`app.css:475-482`) on **all 7 comparable values** and the HLF dark column (DESIGN.md:196-211) on
**0 of 7**. The ratified dark provenance is the mockups synthesis; HLF's dark column was the
superseded 2026-10-24 palette (`9fa70510` replaced it the same day). Erratum appended to
`DESIGN.md`'s ruling block; the dark stat-bg comment in `app.css` carries the same note.
