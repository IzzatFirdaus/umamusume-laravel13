# GP-09 Game-screenshot corpus colour review

2026-11-14. Fourth pass; this one tests at scale what the prior three could not: **does the game
client have a tier ramp, and does it have any dark surfaces at all.** Read-only; one file written.

## Method, sampling frame, limitations

- **Corpus:** `docs/game-screenshots/` — 1159 PNGs, 787 MB, **untracked** (gitignored), capture span
  2026-07-14 02:53 → 2026-07-18 16:19. Registry: `screenshot-registry/inventory.jsonl` — one record
  per frame (`sid` = sha256-12, `ref` = capture timestamp, sha256, dims, dhash, cluster, review
  state, `screen` stratum, `provenance`, `note_refs`). Schema per `GOVERNANCE.md` §README.md.
- **Count verified:** 1159 records == 1159 files on disk; **0 exact duplicates** (per-record
  sha256; COVERAGE.md agrees: "Unique content 1159").
- **Sampling frame (declared before sampling):** (1) dark scan = **all 1159 frames**, metric = mean
  Rec.709 luminance of a 32×32 thumbnail, threshold 0.35. (2) stratified sample N=26 (2.2%): all
  seven `screen` strata with ≤10 members in full (skill-selection 6, race-result 3,
  character-detail 3, legacy-selection 2, team-showdown-confirm 2, career 1) + every 4th training
  frame by `ref` (9 of 36). Deterministic; no visual choice. (3) extraction per frame: dominant
  surfaces = top-3 colours after 4-bit/channel quantisation (share %); ink = mean RGB of the
  darkest 10% of pixels; contrast band = ink vs dominant surface; temperature = R−B of ink.
- **Method limitations stated up front:** the dark-10% ink metric is **contaminated by character
  art** (dark hair/dresses are not ink) — per-frame ink values are directional only; the panel
  and dark-scan metrics are clean. 4-bit quantisation puts surfaces in ±8/255 buckets. 1002 frames
  are `not_reviewed`; their provenance rests on the corpus-level attestation (registry README:
  "original in-game screenshot corpus") + capture-timestamp filenames, not per-frame verification.
- **Web:** no fetch needed — the question this pass answers (in-game dark surfaces) is not one the
  websites can corroborate, and both hosts were source-fetched in pass 08.

## Phase A — inventory, registry schema, provenance

| Item | Finding |
|---|---|
| Registry schema | per-frame JSONL: `sid`/`ref`/sha256/dims/dhash/cluster + review state, `screen` stratum, `provenance{date,detail}`, `note_refs` (category, match class, screen description), `observations` |
| Coverage | 157 `verified` (13.5%, all confidence-high, agent-reviewed 2026-10-11), 1002 `not_reviewed`; 53 frames carry a `screen` value (training 36, skill-selection 6, race-result 3, character-detail 3, legacy-selection 2, team-showdown-confirm 2, career 1); notes annotate 52 frames (exact 50, probable 2); 946 distinct dimensions (173 full 1920×1080; many crops) |
| Provenance | **First-party-measured** for the 157 verified frames (per-frame review attestation). The 1002 unreviewed: corpus-level attestation only — treated as first-party-measured with caveat, none classified Ambiguous enough to exclude from the luminance scan (scan outcome insensitive to single frames) |
| Overlap with prior corpora | **Yes — this corpus is the parent of `_scratch`.** All three probed frames in `accents.json`/`colorprobes*.json` ("Screenshot 2026-07-14 194819", "…202142", "…07-17 230755") are corpus refs (verified 3/3). The 19 sheets and HLF captures are separate bodies. Provenance weight of the `_scratch` anchors rises accordingly: they are measurements *of this corpus* |

## Phase B — dark-screen scan (the question the last two memos could not answer)

Full-corpus mean-luminance distribution: min **0.335**, p10 0.650, **median 0.757**, p90 0.867,
max 0.968. Frames below 0.35: **1 (0.1%)**; below 0.45: **3 (0.3%)**; below 0.28 and 0.20: **0**.

20 darkest (mean lum | ref): 0.335 `2026-07-15 042755`; 0.381 `2026-07-18 000919`; 0.382
`2026-07-15 161727`; then 0.515–0.605 (all `2026-07-15 01xxxx–06xxxx` night-hours captures).

Dark-candidate extraction + identification (registry-traced, vision-identified):

| Ref (sid-traced) | Surfaces (top @share) | Identification |
|---|---|---|
| `2026-07-18 000919` (**verified**: race-result, live-race-board) | `#111111` @11.9%, `#111122`, `#000000`, `#6699CC` | **Diegetic dark scene, not dark UI**: daytime sky on the left half; the darkness is the in-world JRA-style LED results board ("Kyoto 11R FINAL"); the game's UI (skip pill, commentary subtitles, avatar chip) stays light-theme light-on-scene |
| `2026-07-15 042755` (not_reviewed, 813×1079 crop) | `#663344` @5.6%, `#221111`, `#332222`, `#111111` | Victory-celebration popup over the character profile screen: darkness is a **modal scrim** (background uniformly dimmed/blurred; underlying UI still light) |
| `2026-07-15 161727` (not_reviewed, 639×606 crop) | `#997788` @17.7%, `#440011`, `#998888`, `#222233` | Attributes screen crop: darkness is **scene content** (dark-red curtain, character's dark dress); the UI panel beside it is light theme |

**Dark-surface conclusion: the game client has no dark UI theme.** Every dark frame is scene
content, a diegetic object, or a modal-dimming scrim over a light screen. The prior memos'
"no game dark evidence" claim is now **tested at N=1159 and holds** — with one refinement: the
game does use the *dark-scrim-over-light-modal* pattern, which the app also has (overlay shadows).

## Phase C — extraction per stratum (N=26, method above)

| Stratum | N | Dominant UI surfaces (quantised, share) | Contrast band | Ink temp (R−B) |
|---|---|---|---|---|
| training | 9 | `#77CC55`, `#EEEEEE`, `#FFFFFF` | 3.7–9.9:1 | −34…+98 (scene-contaminated) |
| skill-selection | 6 | `#DDDDDD`, `#EEEEEE` | 5.8–8.2:1 | +3…+49 |
| race-result | 3 | `#EEEEEE`, `#FFFFFF` | 5.3–12.5:1 | −1…+15 |
| character-detail | 3 | `#EEEEEE` | 2.0–3.0:1 (scene-dominated) | +30…+43 |
| legacy-selection | 2 | `#EEEEEE` | 4.4–6.5:1 | −7…+3 |
| team-showdown-confirm | 2 | `#EEEEEE` | 2.6–6.6:1 | +6…+35 |
| career | 1 | `#EEEEEE` | 3.0:1 | −52 |

- **Panel language:** every stratum's UI panels sit in the white/near-white band (`#EEEEEE`–
  `#FFFFFF` quantised ≈ the `#F8F8FB` panel the corpus probes measured). Colour variance comes
  from **scenes and accent fills** (training's `#77CC55` ≈ the measured action-green `#7FCC09`),
  not from a surface ramp.
- **No tier contract is measurable**: no stratum shows a stable page/panel/raised/sunken ladder;
  panels are one light surface, with per-screen local accents. **Flat, like the JP website.**
- **Ink:** per-frame dark-10% means are scene-contaminated (stated); where frames are
  text/UI-dominated (race-result, skill-selection), the band lands 5–12:1 with warm-leaning medians
  — consistent with the corpus's probe-measured `#6A5641` on `#F8F8FB` = 6.56:1.

## Phase D answers

1. **Ink temperature.** At the anchor level (probe-measured, named UI regions, RAW-FINDINGS 3.2 via
   `tokens.py`), the game's ink **is** warm brown `#6A5641`/`#482720` — C1a's exact target — now
   measured on the parent corpus of the `_scratch` anchors. My N=26 statistical pass cannot refine
   it (scene contamination, stated) but does not contradict it. **No sampled or probe-measured
   in-game value matches `#625D7D` or `#322A2F`** — the app's current ink family remains its own;
   C1a carries genuine in-game measured support, C1 stays a taste ruling.
2. **Tier ramp.** **None measurable.** The game is one light panel surface + scenes + accents, flat
   like the website. The app's four-tier governance remains an **app invention** (prior memo's
   description stands, now corpus-tested).
3. **Dark mode.** **The game provides no dark-mode evidence.** 1159-frame scan: 0.3% of frames
   below the dark threshold, all scene/scrim, zero dark-UI panels. The answer to "improve dark mode
   from game evidence" is closed: `HLF_reference/dark_mode/` (v0 reference site, ratified) remains
   the only local dark source; the game cannot inform a dark slice.
4. **Contrast posture.** Band on clean (UI-dominated) frames: **~5–12:1, median ≈6.6** — the
   HFW/corpus's soft band, not the app's ~13:1. Banded, not contractual (screenshots are not a
   colour contract).
5. **House style.** Unchanged from 07/08: four-tier ramp with floor guards (1.05/1.25,
   `DesignTokensTest:903`), 41 G-18 pairs, non-text `ink-faint` tier, per-token provenance
   comments. The game has none of this; it is the thing worth protecting.
6. **Pre-alpha intent vs game.** The mockups' warm-cream + dark-ink language and the game's measured
   warm-brown-ink language are **closer to each other** than either is to the shipped `#322A2F`
   ink: the 2026-10-25 synthesis adopted the mockup ink, and it still ships. The corpus's
   warm-brown anchors (`#6A5641`) are the fidelity direction the shipped palette departed from.
   Decision-history fact, recorded for any future fidelity pass.

## Phase E — classification + owner questions

**Classifies as: Confirms the plan, and closes one question.**

- **Confirms:** no change to C2, the guards, or the surface decisions. C1's evidence position is
  sharpened, not overturned: in-game measured support for C1a exists (anchor level); the app's
  current ink matches no in-game measurement.
- **Closes (dark mode):** the game is **light-only at the UI level** — tested at N=1159. The
  dark-mode ruling is no longer open-ended: the game cannot inform a dark slice; the only local
  dark evidence remains the ratified v0 reference site. Whether to build on it is an owner decision
  the corpus has now shown the game cannot contribute to.
- **Owner questions:** (a) **C1a** — take or leave the HFW warm-brown set, now with measured
  in-game anchor support (the strongest fidelity case it has had across the four passes); (b) **dark
  mode** — with "game evidence" eliminated as a path, rule on whether the HLF dark reference justifies
  a dark-mode review at all, or park dark mode indefinitely; (c) none newly surfaced beyond these —
  no new candidate emerges from the corpus.

## Coverage limitations

- Stratified sample = 26/1159 (2.2%); frame was strata-complete for six small strata + stride-4 on
  training; **1002 `not_reviewed` frames were luminance-scanned (100%) but only 26 colour-extracted.**
- The dark-10% ink metric is contaminated by character art; per-stratum ink temperature is
  directional, the panel and dark-scan results are the load-bearing outputs.
- 4-bit quantisation (±8/255) is fine enough for surface-family conclusions, not for exact hexes —
  exact surface values come from the corpus's own declared probes, not this pass.
- Provenance of the 1002 unreviewed frames is corpus-level attestation + capture-timestamp
  filenames; the 157 verified frames carry per-frame review attestation.
- The three dark candidates were vision-identified, not registry-described (two are unreviewed
  crops) — identification confidence high but not registry-primary.
