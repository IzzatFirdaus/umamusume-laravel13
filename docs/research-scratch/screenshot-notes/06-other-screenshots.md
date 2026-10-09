# Other Screens screenshots (merged)

5 frame notes, merged from individual `Screenshot *.md` files.

Each section preserves the original 11-field annotation format.

## Screenshot 2026-07-17 233057

Frame: `docs/game-screenshots/Screenshot 2026-07-17 233057.png`, 1920×1080. Read 2026-10-03 by the Phase B extraction pass.

1. **Category:** other
2. **Screen name:** No title in frame. The right panel is "Log". The left is a spark-resolution event presentation.
3. **Screen purpose:** The player watches the outcome of an inspiration event: which sparks activated, which characters inspired them, and what stat, cap and skill-hint gains resulted.
4. **Information presented:**
   - Left: full-length event art of the run's trainee (not named in frame) on a warm gradient background with sparkles.
   - Speech bubble over the art, four lines: "Guts cap went up by 3.", "Guts went up by 1.", "Gained 1 hint level(s) for Resplendent Red Ace.", "Gained 1 hint level(s) for Corner Adept ○.", "Gained 1 hint level(s) for Productive Plan." (five lines total; numerals and skill names colour-highlighted).
   - Log panel: a running sequence of the same event, line by line: "Inspiration strikes!", "Inspired by Vodka!", "Power spark activated!", "Inspired by Maruzensky!", "Speed spark activated!", "Corner Adept ○ spark activated!", and so on through multiple named inspirations and spark types, then the outcome lines: "Speed cap went up by 3.", "Speed went up by 8.", "Stamina cap went up by 3.", "Stamina went up by 19.", "Power cap went up by 4.", "Power went up by 26.", "Guts cap went up by 3.", "Guts went up by 1.", "Gained 1 hint level(s) for [skill name]." repeated.
   - Skip (green, fast-forward icon) and Quick buttons; menu button.
   - Right nav rail: Jukebox, Sparks, Log (active, green), Career Profile, Agenda, Item Request, Menu (NEW).
5. **Primary action:** Read the event outcome, then Skip or Quick to continue.
6. **Secondary actions:** Scroll the Log; rail navigation.
7. **Layout hierarchy:** Left two thirds: event art with a speech bubble pinned at lower left. Right: the Log panel, a single white card of running prose under a green heading banner. Bottom-centre: Skip and Quick. Nav rail far right.
8. **Visual hierarchy:** The speech bubble first (white on pastel art, bold numbers). The art second. The Log's green banner third; the log text itself is small and secondary to the bubble's summary.
9. **What carries state:** Gains are sentences with the direction word ("up") colour-highlighted; stat names, skill names and inspiration sources are colour-highlighted within the same prose; the spark type is the sentence's subject ("Power spark activated!"); the bubble summarizes what the log records in full.
10. **Absences:** No totals row after the gains; no indication of the trigger odds or why this event fired; the trainee is not identified on screen; no confirmation button other than Skip.
11. **What this screen teaches the app:** An outcome that a numbers-first app would render as a delta table is presented twice, once as art-anchored prose and once as a full running log, so the log is the auditable record and the bubble is its summary. Category is `other` because no enum member covers it; the nearest is `career-progression`.

---

## Screenshot 2026-07-17 235111

Frame: `docs/game-screenshots/Screenshot 2026-07-17 235111.png`, 1920×1080. Read 2026-10-03 by the Phase B extraction pass.

1. **Category:** other
2. **Screen name:** "Edit Team" (top-left screen chip). Right panel: "Scheduled Races".
3. **Screen purpose:** The player assigns team members to the five race categories (Sprint, Mile, Medium, Long, Dirt) ahead of the team competition.
4. **Information presented:**
   - Team status bar: D-rank laurel, "Blue Bloom", "Starting Out" tag, rank "22nd", info button.
   - Header art: five team members in a locker room.
   - Five columns headed "Sprint", "Mile", "Medium", "Long", "Dirt". Each column: an "ACE" slot at top with the member's card and a grade badge (D, D, E, D, C down the row), then filled member slots and empty plus-slots. Each member card shows a portrait, a rarity badge (R or SSR), a grade badge (E, D), a speaker icon, and two aptitude lines such as "Turf A / Sprint A" or "Dirt A / Mile B". The Dirt column has two empty slots and an expand chevron.
   - "Auto-Fill ON" (left) and "Auto-Fill" (right) green buttons; a muted green "Confirm"; Back; Skip; Quick; menu button.
   - Scheduled Races: "Classic Year" active, "Late Jun" highlighted yellow, "Kyoto Daisen" (Scheduled, Early Oct), "Kikuka Sho" (Goal, Late Oct), "Queen Elizabeth Cup" (Scheduled, Early Nov).
   - Nav rail: Agenda active.
5. **Primary action:** Fill the five category columns and Confirm the team.
6. **Secondary actions:** Auto-Fill, expand the Dirt column, calendar controls, rail navigation.
7. **Layout hierarchy:** Left: status bar, art banner, then the five columns spanning the width, then Auto-Fill and Confirm buttons. Right: the Scheduled Races card. Nav rail far right.
8. **Visual hierarchy:** The five green column headers first (they name the decision). The ACE grade badges second. The two Auto-Fill buttons third. The calendar is quieter context.
9. **What carries state:** Column headers are the race categories; a member's slot position encodes their assignment; ACE is a labelled slot with its own grade badge; member quality is the rarity badge (R, SSR) plus a letter grade; aptitude fit is two letter-graded words per card; empty slots are plus placeholders; the expanded/expandable column state is a chevron.
10. **Absences:** No team power or rating total; no per-member contribution estimate; no explanation of what ACE grants; no drag affordance visible (assignment mechanism is not shown in this frame).
11. **What this screen teaches the app:** Team formation is a constraint-satisfaction layout (five categories, limited members) where fit is shown per member as aptitude letters rather than computed into a recommendation, so the tool surfaces raw fit and leaves the assignment to the player. Category is `other` because no enum member covers it; the nearest is `career-progression`.

---

## Screenshot 2026-07-18 001651

Frame: `docs/game-screenshots/Screenshot 2026-07-18 001651.png`, 1920×1080. Read 2026-10-03 by the Phase B extraction pass. Erratum, follow-up audit of 2026-10-03: this note previously claimed to also cover `Screenshot 2026-07-18 134106.png`. That fold is withdrawn. 134106 loads a different photo and reports "Storage 7/120" against this frame's "5/120", so it is not the same screen in the same state, and it now carries its own note.

1. **Category:** other
2. **Screen name:** No heading in frame. The controls name it: "Photo Album", "Hide UI", "Save".
3. **Screen purpose:** The player reviews a saved photo of a race moment and picks a filter before saving.
4. **Information presented:**
   - Main viewer: a race photo (two runners crossing the Kyoto finish, "KYOTO" board, G1 banners).
   - "Hide UI" green button at the viewer's lower right.
   - Filter column, right edge: thumbnail cards labelled "Sepia", "Monochrome", "Original" (selected, green bracket), "Silver", and a further thumbnail cut at the frame edge. Each thumbnail carries the caption "Only gets a high head…".
   - "Back"; a wide green "Save" button with a "Storage 5/120" strip; a "Photo Album" button.
5. **Primary action:** Save the photo with the chosen filter.
6. **Secondary actions:** Hide UI; switch filter; open the Photo Album; Back.
7. **Layout hierarchy:** Large viewer centred left, filter thumbnails stacked in a right rail, action row along the bottom (Back left, Save centre, Album right).
8. **Visual hierarchy:** The photo first. The green Save second. The selected Original thumbnail third (green bracket).
9. **What carries state:** Filter choice is the bracketed thumbnail plus the rendered preview; storage usage is "5/120"; UI visibility is the Hide UI toggle.
10. **Absences:** No capture timestamp, race name or runner identity on the photo view; no delete or share control in frame; the caption text on each thumbnail is truncated and unexplained.
11. **What this screen teaches the app:** A cosmetic surface still carries a hard quota readout (Storage 5/120), so capacity is disclosed at the point of saving rather than in a settings screen. Category is `other` because no enum member covers it; the nearest is `menu`.

---

## Screenshot 2026-07-18 001957

Frame: `docs/game-screenshots/Screenshot 2026-07-18 001957.png`, 1920×1080. Read 2026-10-03 by the Phase B extraction pass.

1. **Category:** other
2. **Screen name:** "Mood Effect" (modal heading). Background: the training screen and Log, dimmed.
3. **Screen purpose:** The player reads the numeric effect of each mood tier on training and racing.
4. **Information presented:**
   - Five rows, each a mood pill with an arrow plus a two-sentence effect:

```text
 - "GREAT" (pink pill, up arrow): "Increases training results by 20%. Increases attributes while running by 4%."
 - "GOOD" (orange pill, up-right arrow): "Increases training results by 10%. Increases attributes while running by 2%."
 - "NORMAL" (gold pill, right arrow): "No change to training results. No change to attributes while running."
 - "BAD" (blue pill, down-left arrow), flagged by a pink "Current →" tab: "Lowers training results by 10%. Lowers attributes while running by 2%." This row's text renders darker than the other four.
 - "AWFUL" (purple pill, down arrow): "Lowers training results by 20%. Lowers attributes while running by 4%."
```

- Close button.
- Background: the training HUD with its five discipline buttons, support rail and the Log's card stack, all dimmed.

1. **Primary action:** Read the table, then Close.
2. **Secondary actions:** None in the modal.
3. **Layout hierarchy:** Centred modal: green heading banner, five stacked rows (pill left, prose right, hairline separators), Close at the bottom. The rest of the client is a dimmed backdrop.
4. **Visual hierarchy:** The "Current →" tab and its darker row first (the only asymmetry in an otherwise uniform table). The five pill colours second, forming a readable ramp. The prose third.
5. **What carries state:** Mood tier identity is the pill's word, arrow direction and colour together; the active tier is a side tab plus heavier text weight; the effect is stated as two percentages, one for training results and one for attributes while running.
6. **Absences:** No pre-race or race-specific modifier beyond the "attributes while running" phrasing; no duration or how-mood-changes guidance; no link to the event that set the current mood.
7. **What this screen teaches the app:** The mood system is a five-row symmetric table (+20/+10/0/-10/-20 training, +4/+2/0/-2/-4 running), so a tool can present mood as a lookup rather than an estimate. The pill ramp is not monotone in hue: GREAT deep pink, GOOD burnt orange, NORMAL gold, BAD **blue**, AWFUL **purple**. The two negative tiers sit on the cool side of the wheel and are nowhere near the positive pink, and the pink "Current →" tab marks the active tier rather than the GREAT tier. Category is `other` because no enum member covers it; the nearest is `training`.

---

## Screenshot 2026-07-18 134106

Frame: `docs/game-screenshots/Screenshot 2026-07-18 134106.png`, 1920×1080. Read 2026-10-03 by the Phase B follow-up dispatch, which un-folded this frame from `Screenshot 2026-07-18 001651.md`. Canonical cluster: a singleton in `docs/design-research/_scratch/clusters.json`, so the signature clustering treats it as its own screen state.

1. **Category:** other
2. **Screen name:** No heading in frame. The controls name it: "Photo Album", "Hide UI", "Save".
3. **Screen purpose:** The player reviews a saved photo of a race moment and picks a filter before saving.
4. **Information presented:**
   - Main viewer: a race photo of a field rounding a bend on turf. A red-and-white distance post marked "6" stands at the rail on the left; a tall hedge fills the background; one runner in dark kit with a blue rose is separated in the foreground left, with the pack of roughly ten runners bunched behind the rail to the right.
   - "Hide UI" green button at the viewer's lower right.
   - Filter column, right edge: thumbnail cards labelled "Sepia", "Monochrome", "Original" (selected, green bracket), "Silver", and a further thumbnail cut at the frame edge. Each thumbnail previews this photo under its own filter and carries the truncated caption "Only gets a high head…".
   - "Back"; a wide green "Save" button with a "Storage 7/120" strip; a "Photo Album" button.
5. **Primary action:** Save the photo with the chosen filter.
6. **Secondary actions:** Hide UI; switch filter; open the Photo Album; Back.
7. **Layout hierarchy:** Large viewer centred left, filter thumbnails stacked in a right rail, action row along the bottom (Back left, Save centre, Album right).
8. **Visual hierarchy:** The photo first. The green Save second. The selected Original thumbnail third (green bracket).
9. **What carries state:** Filter choice is the bracketed thumbnail plus the rendered preview and the four rail thumbnails; storage usage is "7/120"; UI visibility is the Hide UI toggle.
10. **Absences:** No capture timestamp, race name or runner identity on the photo view; no delete or share control in frame; the thumbnail caption is truncated and unexplained; no indication of which race the photo came from.
11. **What this screen teaches the app:** The album's storage counter is a live quota that advances between captures of the same screen (5/120 in frame 001651, 7/120 here), so a tool that mirrors saved photos has to read the counter rather than infer a count from how many photos it has seen. Category is `other` because no enum member covers it; the nearest is `menu`.

---

