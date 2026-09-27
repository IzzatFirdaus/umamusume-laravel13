# SCREENSHOT-MANIFEST

Triage of `docs/game-screenshots/` for scenario and screen-type coverage. Generated 2026-09-27.
Method and per-screen visual detail live in `RAW-FINDINGS.md`; this file is the coverage index.

## How this was produced

Three passes, none of which reads all 1,160 frames:

1. **Signature pass** (`_scratch/triage.py`) — dimensions, mean colour, luminance and a 4x4 cell grid for every frame. Output `signatures.json`.
2. **Perceptual clustering** (`_scratch/cluster.py`) — dHash, Hamming threshold 6, 60-frame rolling window. Collapses 1,160 frames to **751 distinct screens**. Output `clusters.json`.
3. **Scenario-chip montage** (`_scratch/scenario_probe.py`) — crops the turn-chip region from 96 frames sampled at a fixed stride across the whole capture span and tiles them into one sheet (`_scratch/scenario_chips.png`). This is what actually answers the scenario question, and it is why an earlier claim in this package had to be withdrawn.

## Corpus shape

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

## Scenario coverage

This is the section that matters for the comparative request, and the honest answer is narrower than the question.

| Scenario | Evidence in corpus | Coverage |
|---|---|---|
| **Unity Cup** | Turn chips reading `Until the Unity Cup` / `Unity Cup Begins at End of Turn` across the 96-frame montage; `TEAM RANK B 11/16` and `Result Pts` counters; Team Showdown opponent select | **Dominant.** Deep coverage of training HUD, choices, log, calendar, team screens |
| **Ura Finale** | `012428` carries a `Place 1st in URA Finals` goal with a `FINISHED` state; `014154` shows the same goal line with `Power Lvl 5` | **Minimal — two frames, not a coverage basis.** Both are known only for their goal line; nothing else about URA's surfaces is recorded in this corpus, and `014154`'s `Power Lvl 5` implies a training screen that has not been described or cropped here. "Present" means the label is legible in the corpus, not that URA's UI is represented — designing URA-specific layout from this would be designing from two goal-line crops. Reading those two frames in full is the cheap way to widen this row |
| **Trackblazer / Climax** | None found | **Absent** |
| **Our Grand Concert** | None found | **Absent** |

**A three-way scenario comparison cannot be performed on this corpus.** Two of the four Global scenarios have no frames at all. A `SCREENSHOT-MANIFEST` that listed URA, Unity Cup and Trackblazer clusters with comparable counts would be reporting structure that is not there.

**Correction on the record.** An earlier revision of `RAW-FINDINGS.md` and `DESIGN.md` asserted that the entire corpus was one scenario. That was an overclaim from roughly ten inspected frames and is withdrawn: Ura Finale material exists. The claim was replaced by the table above, which is derived from a 96-frame stratified sample.

## Screen-type coverage

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

## Facility activity names observed

Extracted from the discipline banner across the montage. **Scope of the claim, corrected 2026-09-27:** every frame that shows one of these names is a **Unity Cup** frame (see the coverage table, which records zero Trackblazer and zero Our Grand Concert training HUDs). So the list evidences that activity names vary **per facility within Unity Cup**, and that the banner carries them. It does **not** evidence that facility layouts differ *between* scenarios — that comparison is not available on this corpus, and this file's own methodology line says so. Any artifact that cited these six names as cross-scenario evidence was over-reading a single scenario's data; the claim is corrected in `CONSTRAINTS.md` D-187 and `DESIGN.md` §6.4b and §6.22, and the rule D-187 states (a banner without its activity line is incomplete) is unaffected.

`Breaststroke`, `Freestyle`, `Long-Distance Swimming` (Stamina, pool), `Incline` (Guts), `Running` (Speed), `Dirt` (Power) — all Unity Cup.

## Scenario guides fetched from the web, same date

| Scenario | Guide | Retrieved | Effect on the design |
|---|---|---|---|
| Ura Finale | `game8.co/.../archives/536520` | **Yes, in full** | Caps are `1200 base + 200`; gains past 1200 halve; camp recurs in Classic **and** Senior; scenario NPCs and a Scenario Link Character; scenario reworked 2026-07-01. Drives `CONSTRAINTS.md` D-211 to D-218 |
| Unity Cup | `archives/545572` | **No**, redirected off the scenario page | Nothing. Unity Cup claims rest on the screenshot corpus only |
| Trackblazer | `archives/580723` | **No**, redirected to a JP training-effects page | Nothing. Still no Trackblazer evidence of any kind |

The scenario-comparison gap identified above is therefore **unchanged**: one of three guides was retrieved, and the corpus has no Trackblazer frames at all. A three-way comparison remains impossible.

## Not present in the corpus

Scouts / gacha screens, support-card composition, settings, Trackblazer chrome, Our Grand Concert chrome, and any non-Unity-Cup training loop. Where the design needs these it borrows the grammar of the screens above rather than inventing new patterns, and `CONSTRAINTS.md` D-193 requires any such design to name its evidence rather than imply coverage.
