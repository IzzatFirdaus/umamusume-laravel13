# Training screenshots (merged)

14 frame notes, merged from individual `Screenshot *.md` files.

Each section preserves the original 11-field annotation format.

## Screenshot 2026-07-14 194819

Frame: `docs/game-screenshots/Screenshot 2026-07-14 194819.png`, 1920 by 1080. Read 2026-10-03 by the Phase B extraction pass.

1. **Category:** training
2. **Screen name:** Training (top-left screen chip). The date strip reads "Junior Year Late Aug"; the turn chip reads "9 turn(s) left" and a blue chip below it reads "8 turn(s) Until the Unity Cup".
3. **Screen purpose:** The player picks one of five training disciplines to raise the trainee's stats for this turn.
4. **Information presented:**
   - Goal banner: "Earn 3000 fans" with progress "1,943 fan(s) to go" and a Details button.
   - Energy gauge: a horizontal multicolour bar, roughly two thirds filled, with a "GOOD" mood pill (orange up-right arrow) beside it.
   - Green banner pair: "Power Lvl 1" and "Dirt".
   - Support card rail, right edge: three circular card avatars stacked vertically, each with a small blue bond gauge and one or more spark icons.
   - Stat band: Speed 283/1318 (grade E+), Stamina 116/1316 (F), Power 274/1320 (E+), Guts 118/1304 (F), Wit 107/1800 (F), Skill Pts 246.
   - Gain bubbles: +5 above the Speed column, +13 above the Power column, +5 above Skill Pts; two more bubbles (+2, +1) float above the band and their column anchors are ambiguous between Stamina, Guts and Skill Pts. The +13 sits over Power, the discipline whose button is selected.
   - Failure badge: "Failure 0%".
   - Discipline buttons, bottom: Speed Lvl 1, Stamina Lvl 1, Power Lvl 1, Guts Lvl 1, Wit Lvl 1, each with an icon (shoe, heart, dumbbell, megaphone, pencil).
5. **Primary action:** Tap one of the five discipline buttons to commit that training for the turn.
6. **Secondary actions:** Open goal Details; inspect the support card avatars on the rail.
7. **Layout hierarchy:** Header stack top-left (date strip over turn chip over Unity Cup countdown chip) with the goal banner to its right and the Energy gauge and mood pill under the banner. Left edge carries two green state banners ("Power Lvl 1", "Dirt"). The centre is the trainee artwork. The right edge is the support card rail. The stat band spans the lower third, the Failure badge hangs under it, and the five discipline buttons run along the bottom edge.
8. **Visual hierarchy:** The trainee artwork first (largest element, centre). The stat band second: white rounded boxes, large numerals, orange gain bubbles. The discipline buttons third: saturated green circles with white labels, the selected Power button raised above the row with yellow double-chevron arrows beneath it.
9. **What carries state:** Energy is a bar length with a colour gradient; mood is a colour word pill with a direction arrow; each stat is number, denominator and letter grade; facility levels are "Lvl N" text inside each discipline button; the selected discipline is position (raised button plus chevrons); Failure risk is a percentage badge; support card bond is a segmented blue gauge per card; the turn and countdown chips are raw numbers; goal progress is "fans to go".
10. **Absences:** No success range or outcome preview beyond the failure percentage; no per-support-card contribution breakdown behind the gain bubbles; no history of previous turns; no cancel or back control in frame.
11. **What this screen teaches the app:** A training decision can be fully evaluated from four channels (stat value, grade letter, per-stat gain bubble, failure percentage) without opening any other screen.

---

## Screenshot 2026-07-14 202142

Frame: `docs/game-screenshots/Screenshot 2026-07-14 202142.png`, 1920 by 1080. Read 2026-10-03 by the Phase B extraction pass.

1. **Category:** training
2. **Screen name:** Training (top-left screen chip implied by layout; the chip itself reads "Junior Year Late Dec" on the date strip). Turn chip "1 turn(s) left"; blue chip "Unity Cup Begins at End of Turn".
3. **Screen purpose:** The player picks the final training before the scenario's deciding phase begins.
4. **Information presented:**
   - Goal banner: "Earn 3000 fans" with orange sub-line "Goal Achieved!" and a Details button.
   - Energy gauge, roughly half filled, with a "GREAT" mood pill (pink, up-right arrow).
   - Green banner pair: "Stamina Lvl 1" and "Breaststroke".
   - Support card rail, right edge: five circular card avatars stacked vertically, each with a bond gauge; four carry spark icons, one carries a blue icon.
   - Stat band: Speed 338/1318 (D), Stamina 144/1316 (F), Power 317/1320 (D), Guts 130/1304 (F), Wit 109/1800 (F), Skill Pts 307.
   - Gain bubbles: +22 over the Speed column with +5 above it, +14 over the Power column with +2 above it, +9 over Skill Pts with +4 above it. Stamina and Guts show no bubbles.
   - Failure badge: "Failure 0%".
   - Discipline buttons: Speed Lvl 1, Stamina Lvl 1 (selected, raised, yellow double-chevron arrows, spark icon), Power Lvl 1, Guts Lvl 1, Wit Lvl 1.
5. **Primary action:** Tap a discipline button to commit the turn's training.
6. **Secondary actions:** Open goal Details; inspect support cards on the rail.
7. **Layout hierarchy:** Header stack top-left (date strip, turn chip, Unity Cup chip), goal banner to its right, Energy gauge and mood pill under the banner. Left edge carries the green facility and activity banners. Centre is the trainee artwork; right edge is the five-card support rail. Stat band across the lower third with the Failure badge below, discipline buttons along the bottom.
8. **Visual hierarchy:** Trainee art first. The stacked gain bubbles second: three orange pairs, largest numerals in the frame. Stat band third. The selected Stamina button reads through position (raised, chevrons) rather than colour, since all five buttons share the same green.
9. **What carries state:** The goal's achieved state is an orange sub-line replacing progress text; mood is a colour word pill with arrow; each stat is value/denominator/grade; gain bubbles are stacked pairs where the upper smaller bubble is the bonus component and the lower larger bubble the base gain; the selected discipline is the raised button with chevrons; Unity Cup timing is a countdown chip; support bond is a segmented gauge per card.
10. **Absences:** No confirmation step between tapping a discipline and the turn resolving; no per-card source attribution on the gain bubbles; no hint of what Breaststroke (the named activity) changes relative to a base training; no energy cost shown.
11. **What this screen teaches the app:** An achieved goal switches the goal row from progress to a state line while the HUD keeps operating normally, so completion of an objective is a banner change, not a mode change.

---

## Screenshot 2026-07-17 230755

Frame: `docs/game-screenshots/Screenshot 2026-07-17 230755.png`, 1920×1080. Read 2026-10-03 by the Phase B extraction pass.

**Obstruction:** a Snipping Tool notification ("Screenshot copied to clipboard", with a thumbnail and a "Mark-up and share" button) overlays the lower right of the Scheduled Races panel and part of the nav rail. Everything behind that rectangle is undescribed.

1. **Category:** training
2. **Screen name:** "Training" (top-left screen chip). Right panel: "Scheduled Races".
3. **Screen purpose:** The player picks a training discipline, with Stamina currently selected.
4. **Information presented:**
   - Turn chip "3 turn(s) left"; date strip "Classic Year Early Feb"; blue chip "9 turn(s) Until the Unity Cup".
   - Goal banner: "Place top 5 in Spring S.", orange sub-line "Entry criteria met!", Details button.
   - Energy gauge about two thirds filled with a "GOOD" mood pill (orange up-right arrow).
   - Blue banner pair: "Stamina Lvl 2" (heart icon) and "Freestyle". Both banners render blue in this frame, where the same banners render green in the 2026-07-14 frames; the accent tint varies.
   - Support card rail: four circular card avatars with bond gauges and spark or heart icons.
   - Stat band: Speed 302/1318 (D), Stamina 292/1316 (E+), Power 254/1330 (E+), Guts 344/1304 (D), Wit 145/1800 (F), Skill Pts 116.
   - Gain bubbles: +17 with +2 above it over the Stamina column, +11 over the Power column, +6 with +1 above it over the Wit/Skill Pts gap.
   - Failure badge: "Failure 0%".
   - Discipline buttons: Speed Lvl 2, Stamina Lvl 2 (selected, raised, yellow double-chevron arrows), Power Lvl 2, Guts Lvl 2, Wit Lvl 2. All five render blue in this frame.
   - Scheduled Races: tabs with "Classic Year" active; half-month grid with "Early Feb" highlighted yellow; "Spring Stakes" (Goal, Late Mar), "Kyoto Shimbun Hai" (Scheduled, Early May), "Tokyo Yushun" (Goal, Late May), "Kyoto Daisen" (Scheduled, Early Oct), "Kikuka Sho" (Goal, Late Oct, partially behind the obstruction), "Queen Elizabeth Cup" (Scheduled, Early Nov); Reset and "My Agendas".
   - Back, "Skip" (green with fast-forward icon), Quick, menu button.
5. **Primary action:** Tap a discipline button to commit training.
6. **Secondary actions:** Back, Skip, Quick, goal Details, calendar Reset and My Agendas, rail navigation.
7. **Layout hierarchy:** Same shell as the career menu: header stack top-left, goal banner, Energy row, blue facility banners on the left edge, trainee art centre, support rail right of the art, stat band lower third, Failure badge, discipline buttons along the bottom. Right half is the Scheduled Races card. Nav rail far right, partially obscured at its lower end.
8. **Visual hierarchy:** Trainee art first. Stacked gain bubbles second. Stat band third. The blue accent shifts the whole left half's temperature; the selected Stamina button is again position-coded (raised plus chevrons), not colour-coded.
9. **What carries state:** Identical encodings to the other training frames: bar-and-pill for Energy and mood, value/denominator/grade triples, stacked bubble pairs for base and bonus gains, raised button for selection, pennant and Scheduled pill for calendar state, yellow cell for the current half-month.
10. **Absences:** No legend for the banner tint; the activity name ("Freestyle") has no visible explanation; no per-card gain attribution; the obstruction hides whatever the Late Aug and Item Request region carried.
11. **What this screen teaches the app:** The training HUD's accent colour is not fixed chrome but a per-trainee or per-state tint, so any faithful layout must take its accent as data rather than hardcode it.

---

## Screenshot 2026-07-17 230902

Frame: `docs/game-screenshots/Screenshot 2026-07-17 230902.png`, 1920×1080. Read 2026-10-03 by the Phase B extraction pass.

**Obstruction:** the same Snipping Tool notification as the 230755 frame, covering the lower right of the Scheduled Races panel and part of the nav rail.

1. **Category:** training
2. **Screen name:** "Training" (top-left screen chip). Right panel: "Scheduled Races".
3. **Screen purpose:** The player picks a training discipline, with Power currently selected.
4. **Information presented:**
   - Turn chip "2 turn(s) left"; date strip "Classic Year Late Feb"; blue chip "8 turn(s) Until the Unity Cup".
   - Goal banner: "Place top 5 in Spring S.", orange sub-line "Entry criteria met!", Details button.
   - Energy gauge about one third filled, "GOOD" mood pill (orange up-right arrow).
   - Blue banner pair: "Power Lvl 2" (dumbbell icon) and "Squats".
   - Support card rail: five circular avatars with bond gauges; the topmost carries a red exclamation badge, the others carry spark or heart icons.
   - Stat band: Speed 307/1318 (D), Stamina 311/1316 (D), Power 259/1330 (E+), Guts 360/1304 (D+), Wit 145/1800 (F), Skill Pts 123.
   - Gain bubbles: +7 with +1 above it over the Speed column, +15 with +4 above it over the Power column, +7 with +3 above it over the Wit/Skill Pts gap.
   - Failure badge: "Failure 0%".
   - Discipline buttons: Speed Lvl 2, Stamina Lvl 2, Power Lvl 2 (selected, raised, yellow double-chevron arrows), Guts Lvl 2, Wit Lvl 2, all blue in this frame.
   - Scheduled Races: "Classic Year" active; "Late Feb" highlighted yellow; "Spring Stakes" (Goal, Late Mar), "Kyoto Shimbun Hai" (Scheduled, Early May), "Tokyo Yushun" (Goal, Late May), "Kyoto Daisen" (Scheduled, Early Oct), "Kikuka Sho" (Goal, Late Oct, partially behind the obstruction), "Queen Elizabeth Cup" (Scheduled, Early Nov).
5. **Primary action:** Tap a discipline button to commit training.
6. **Secondary actions:** Back, Skip, Quick, goal Details, calendar controls, rail navigation.
7. **Layout hierarchy:** Identical shell to frame 230755 (header stack, goal banner, Energy row, blue banners left edge, trainee art centre, support rail, stat band, Failure badge, five discipline buttons; Scheduled Races card right; nav rail far right). Refer to that note for the sketch.
8. **Visual hierarchy:** Trainee art, then the +15 gain pair, then the stat band. The red exclamation badge on the top support card is a small element that draws above its weight through saturation.
9. **What carries state:** As in 230755, plus: a support card's attention state is a red exclamation badge on the avatar; Energy depletion reads through shorter bar length with the same gradient.
10. **Absences:** As in 230755; additionally the exclamation badge's cause is not shown anywhere in frame.
11. **What this screen teaches the app:** The same HUD surface carries a per-card alert channel, so support cards are not passive inventory but elements that can demand attention during the training decision.

---

## Screenshot 2026-07-18 001811

Frame: `docs/game-screenshots/Screenshot 2026-07-18 001811.png`, 1920×1080. Read 2026-10-03 by the Phase B extraction pass. This is the largest cluster in the corpus (58 frames). Source: `docs/design-research/_scratch/clusters.json`, SHA-256 `0ce49656c816c87befc2787c7007c00845806241cc6f350bab9e3bd04d7c4d1d`, where this frame is the `rep` of the cluster with `count` 58. A revision of this note replaced the 58 with a 4-frame claim measured against a temporal clustering; that replacement was wrong and is reverted here.

1. **Category:** training
2. **Screen name:** "Training" (top-left chip). Right panel: "Log".
3. **Screen purpose:** The player picks a training discipline with Guts selected, while the Log shows the conversation that just happened.
4. **Information presented:**
   - Turn chip "7 turn(s) left"; date strip "Classic Year Early Dec"; blue chip "1 turn(s) Until the Unity Cup".
   - Goal banner: "Place top 3 in Nikkei Sho", orange sub-line "Entry criteria met!", Details.
   - Energy gauge about three quarters filled, with a "BAD" mood pill (blue, down-left arrow).
   - Orange banner pair: "Guts Lvl 3" (megaphone icon) and "Dance Practice".
   - Support card rail: six circular avatars with bond gauges; the topmost has no spark icon, the rest carry spark, book, globe or flame icons.
   - Stat band: Speed 385/1321 (D+), Stamina 443/1319 (C), Power 396/1344 (D+), Guts 468/1307 (C), Wit 208/1800 (E), Skill Pts 116.
   - Gain bubbles: +3 with +3 above it over Speed, +3 with +3 above it over Stamina, +17 with +11 above it over Guts, +6 with +4 above it over the Wit/Skill Pts gap.
   - Failure badge: "Failure 0%".
   - Discipline buttons: Speed Lvl 3, Stamina Lvl 3, Power Lvl 3, Guts Lvl 3 (selected, raised, yellow chevrons), Wit Lvl 3, all orange-tinted in this frame.
   - Log panel: dialogue cards from two named characters, a narrative card ("Rice Shower is unable to answer, Bourbon's words shaking her to her very core."), and an outcome card "Guts went up by 5."
   - Back, Skip, Quick, menu; nav rail with Log active.
5. **Primary action:** Tap a discipline button.
6. **Secondary actions:** Back, Skip, Quick, goal Details, scroll the Log, rail navigation.
7. **Layout hierarchy:** Same training shell as frame 133312 (header stack, goal banner, Energy row, facility banners left, art centre, support rail, stat band, Failure badge, five buttons; Log right; nav rail far right). Refer to that note for the sketch.
8. **Visual hierarchy:** The +17 bubble pair first (largest numerals, over the selected column). The blue BAD pill second, because it is the only cool-coloured element in a warm frame. The Log's narrative card third.
9. **What carries state:** Mood is a colour word pill with a direction arrow, and the BAD state renders blue with a down-left arrow where GREAT renders pink and GOOD orange with up-right arrows; the discipline button tint follows the frame's accent (orange here); the Log distinguishes speech (name-headed cards) from narration (no name) from outcome (signed delta sentence).
10. **Absences:** No numeric energy cost for the selected training; no mood effect magnitude; the narrative card has no styling that separates it from dialogue beyond the absence of a name.
11. **What this screen teaches the app:** The three-card log grammar (named speech, unnamed narration, signed outcome) is the client's own event record, so a tool mirroring it needs three entry kinds, not one message list.

---

## Screenshot 2026-07-18 001817

Frame: `docs/game-screenshots/Screenshot 2026-07-18 001817.png`, 1920×1080. Read 2026-10-03 by the Phase B extraction pass.

**Obstruction:** a Snipping Tool notification covers the lower right of the Log and the nav rail's lower half.

1. **Category:** training
2. **Screen name:** "Training" (top-left chip). Right panel: "Log".
3. **Screen purpose:** The player picks training with Wit selected, on the same turn as frame 001811.
4. **Information presented:**
   - Turn chip "7 turn(s) left"; date strip "Classic Year Early Dec"; blue chip "1 turn(s) Until the Unity Cup".
   - Goal banner "Place top 3 in Nikkei Sho", orange sub-line "Entry criteria met!", Details.
   - Energy gauge with a "BAD" mood pill (blue, down-left arrow).
   - Orange banner pair: "Wit Lvl 3" (pencil icon) and "Quiz".
   - Background art: a classroom with a chalkboard reading "first, the rest nowhere." (the gym of frame 001811 is replaced).
   - Support card rail: four circular avatars; one carries a "MAX" label across its bond gauge; icons include a flame, a heart, a globe.
   - Stat band: identical values to frame 001811 (Speed 385/1321 D+, Stamina 443/1319 C, Power 396/1344 D+, Guts 468/1307 C, Wit 208/1800 E, Skill Pts 116).
   - Gain bubbles: +2 with +2 above it over Speed, +9 with +7 above it over the Wit/Skill Pts gap. Stamina, Power and Guts show none.
   - Failure badge "Failure 0%"; discipline buttons Speed Lvl 3, Stamina Lvl 3, Power Lvl 3, Guts Lvl 3, Wit Lvl 3 (selected, raised, chevrons).
   - Log: the same Mihono Bourbon, Rice Shower, narrative and outcome cards as frame 001811.
5. **Primary action:** Tap a discipline button.
6. **Secondary actions:** Back, Skip, Quick, Details, rail navigation.
7. **Layout hierarchy:** Same shell as frame 001811; refer to that note for the sketch.
8. **Visual hierarchy:** The trainee's thinking pose against the classroom first, the +9 bubble pair second, the "MAX" bond label third (small but unique).
9. **What carries state:** The activity name and the background art both change with the selected discipline; a support card's bond ceiling is a "MAX" label over the gauge rather than a full bar; gain bubbles appear only on the columns the selected training affects.
10. **Absences:** No indication of what "Quiz" contributes beyond the Wit gain; no per-card attribution for the +7 bonus; the chalkboard text is decorative with no UI role.
11. **What this screen teaches the app:** Selecting a discipline restyles the whole scene (activity name, background, button tint), so the choice is communicated environmentally and not only by the raised button.

---

## Screenshot 2026-07-18 001823

Frame: `docs/game-screenshots/Screenshot 2026-07-18 001823.png`, 1920×1080. Read 2026-10-03 by the Phase B extraction pass.

**Obstruction:** a Snipping Tool notification covers the lower right of the Log and the nav rail's lower half.

1. **Category:** training
2. **Screen name:** "Training" (top-left chip). Right panel: "Log".
3. **Screen purpose:** The player picks training with Speed selected, on the same turn as frames 001811 and 001817.
4. **Information presented:**
   - Turn chip "7 turn(s) left"; date strip "Classic Year Early Dec"; blue chip "1 turn(s) Until the Unity Cup".
   - Goal banner "Place top 3 in Nikkei Sho", orange sub-line "Entry criteria met!", Details.
   - Energy gauge with a "BAD" mood pill.
   - Orange banner pair: "Speed Lvl 3" (shoe icon) and "Exercise Bike".
   - Background: gym with treadmills and a fan bike.
   - Support card rail: three circular avatars only (six in frame 001811, four in 001817), each with a bond gauge and a spark or globe icon.
   - Stat band: same values as 001811 and 001817.
   - Gain bubbles: +9 with +4 above it over Speed, +4 with +1 above it over the Stamina/Power gap, +3 with +3 above it over the Wit/Skill Pts gap.
   - Failure badge "Failure 0%"; discipline buttons all "Lvl 3", Speed selected (raised, chevrons).
   - Log: the same card sequence as 001811, with dialogue cards headed "Mihono Bourbon" and "Rice Shower" and the narrative card "Rice Shower is unable to answer, Bourbon's words shaking her to her very core."
5. **Primary action:** Tap a discipline button.
6. **Secondary actions:** Back, Skip, Quick, Details, rail navigation.
7. **Layout hierarchy:** Same shell as frame 001811; refer to that note for the sketch.
8. **Visual hierarchy:** The +9 bubble first, the raised Speed button second, the shorter support rail noticeable only by comparison with the sibling frames.
9. **What carries state:** The support rail's membership changes with the selected discipline; the activity name and background change with it; stats and log stay fixed because the turn has not resolved.
10. **Absences:** No label explaining why three cards appear here and six on Guts; no per-card gain attribution inside the +4 bonus bubble.
11. **What this screen teaches the app:** The support card rail is a filtered view of the deck for the selected discipline, not a static deck strip, so any tool that mirrors it must treat the rail as derived state.

---

## Screenshot 2026-07-18 133312

Frame: `docs/game-screenshots/Screenshot 2026-07-18 133312.png`, 1920×1080. Read 2026-10-03 by the Phase B extraction pass.

1. **Category:** training
2. **Screen name:** "Training" (top-left screen chip), with the "Log" panel open on the right half.
3. **Screen purpose:** The player reviews the training HUD with Speed selected while the right half shows the run's dialogue and event history.
4. **Information presented:**
   - Turn chip: "1 turn(s) left"; date strip "Senior Year Early Apr"; blue chip "5 turn(s) Until the Unity Cup".
   - Goal banner: "Place 1st in Tenno Sho (Spring)" with orange sub-line "Entry criteria met!" and a Details button.
   - Energy gauge with a "GREAT" mood pill (pink, up-right arrow).
   - Orange banner pair: "Speed Lvl 3" with a shoe icon and "Exercise Bike".
   - Support card rail, right of the artwork: six circular card avatars, each with a bond gauge and spark or heart icons.
   - Stat band: Speed 467/1325 (C), Stamina 533/1358 (C+), Power 455/1308 (C), Guts 531/1308 (C), Wit 271/1800 (E+), Skill Pts 139.
   - Gain bubbles: +19 with +17 stacked above it over the Speed column (the upper bubble carries a spark icon); +7 and +7 over the Stamina/Power gap; +6 and +7 over the Guts/Wit gap. The column anchors of the middle and right pairs are ambiguous at this resolution.
   - Failure badge: "Failure 0%".
   - Discipline buttons: Speed Lvl 3 (selected, raised, double-chevron arrows), Stamina Lvl 3, Power Lvl 3, Guts Lvl 3, Wit Lvl 3.
   - Log panel: name-headed dialogue cards, "Rice Shower" on four of the six visible cards and "Haru Urara" and "Mihono Bourbon" on one each; Haru Urara's line reads "W-wait. Rice!"; a final system card "Mood remains Great." and "Blue Rose Closer leveled up." with the second line colour-highlighted.
   - Right nav rail: Jukebox, Sparks, Log (active, green), Career Profile, Agenda, Item Request, Menu (NEW).
5. **Primary action:** Tap a discipline button to commit training.
6. **Secondary actions:** Back; Skip Off and Quick toggles; open goal Details; scroll the Log; navigate the rail.
7. **Layout hierarchy:** Left half is the training HUD: header stack top-left, goal banner right of it, Energy and mood under the banner, orange facility banners on the left edge, trainee art centre, support rail right of the art, stat band across the lower third, Failure badge under it, discipline buttons along the bottom. Right half is the Log: green heading banner over a vertical stack of white dialogue cards, the system card last. Nav rail far right.
8. **Visual hierarchy:** The trainee art first. The stacked orange gain bubbles second (largest numerals on screen). The stat band third. The Log's green banner and white cards sit at lower visual priority than the training half despite occupying equal area.
9. **What carries state:** Mood is a colour word pill with arrow; goal criteria is an orange sub-line under the goal text; facility level is "Lvl N" text plus icon on an orange banner; the selected discipline is the raised button; stats are value/denominator/grade triples; gain bubbles are stacked pairs where the upper bubble with a spark icon is the bonus component; the Log marks speakers by name headings and system outcomes by a colour-highlighted second line.
10. **Absences:** No timestamps or turn numbers on Log entries; no filter or phase grouping visible in this frame; the entry criteria line does not show which criteria; no energy cost readout for the selected training.
11. **What this screen teaches the app:** The run's narrative log and its decision surface are peers on one screen, so an outcome the player just read (mood kept, a skill card leveled) is visible at the moment the next training choice is made.

---

## Screenshot 2026-07-18 133322

Frame: `docs/game-screenshots/Screenshot 2026-07-18 133322.png`, 1920×1080. Read 2026-10-03 by the Phase B extraction pass.

**Obstruction:** a Snipping Tool notification covers the lower right of the Log and the nav rail's lower half.

1. **Category:** training
2. **Screen name:** "Training" (top-left chip). Right panel: "Log".
3. **Screen purpose:** The player compares disciplines on the same turn, here with Power selected.
4. **Information presented:**
   - Turn chip "1 turn(s) left"; date strip "Senior Year Early Apr"; blue chip "5 turn(s) Until the Unity Cup".
   - Goal banner "Place 1st in Tenno Sho (Spring)", orange "Entry criteria met!", Details.
   - Energy gauge about half filled, "GREAT" mood pill (pink, up arrow).
   - Orange banner pair: "Power Lvl 3" (dumbbell icon) and "Sit-Ups".
   - Support card rail: three circular avatars with bond gauges and spark, globe or flame icons.
   - Stat band: Speed 467/1325 (C), Stamina 533/1322 (C), Power 455/1358 (C), Guts 531/1308 (C), Wit 271/1800 (E+), Skill Pts 139.
   - Gain bubbles: +6 with +15 above it over the Stamina/Power area, +5 over the Wit/Skill Pts gap.
   - Failure badge "Failure 0%"; discipline buttons all "Lvl 3", Power selected (raised, chevrons).
   - Log: the same card sequence as frame 133312 (Rice Shower, Haru Urara, Mihono Bourbon, then "Mood remains Great. Blue Rose Closer leveled up.").
5. **Primary action:** Tap a discipline button.
6. **Secondary actions:** Back, Skip Off, Quick, Details, rail navigation.
7. **Layout hierarchy:** Same shell as frame 133312. Refer to that note for the sketch.
8. **Visual hierarchy:** The +15 bubble first. The raised Power button second. The stat band third.
9. **What carries state:** Same turn with a different selection: stats and log are unchanged, while the activity name, the bubble set, the rail membership and the facility banner all move with the selection.
10. **Absences:** No comparison table showing all five disciplines' gains at once; no energy cost per discipline; the bubble-to-column mapping is ambiguous where bubbles sit between columns.
11. **What this screen teaches the app:** Discipline selection is a preview interaction the player repeats before committing, so a tool that models a turn must store the chosen discipline and its preview separately rather than one number.

---

## Screenshot 2026-07-18 134917

Frame: `docs/game-screenshots/Screenshot 2026-07-18 134917.png`, 1920×1080. Read 2026-10-03 by the Phase B extraction pass.

**Obstruction:** a Snipping Tool notification covers the lower right of the Scheduled Races panel and the nav rail's lower half.

1. **Category:** training
2. **Screen name:** "Training" (top-left chip). Right panel: "Scheduled Races".
3. **Screen purpose:** The player picks training with Guts selected, late in the Senior Year.
4. **Information presented:**
   - Turn chip "3 turn(s) left"; date strip "Senior Year Early May"; blue chip "3 turn(s) Until the Unity Cup".
   - Goal banner "Place top 3 in Takarazuka Kinen", orange "Entry criteria met!", Details.
   - Energy gauge about half filled, "GREAT" mood pill.
   - Orange banner pair: "Guts Lvl 3" (megaphone icon) and "Dance Practice".
   - Support card rail: six circular avatars; one carries a "MAX" label across its bond gauge and an iridescent ring around the portrait; the others carry spark, book, globe or flame icons.
   - Stat band: Speed 474/1325 (C), Stamina 536/1322 (C), Power 458/1358 (C), Guts 539/1308 (C+), Wit 292/1800 (E+), Skill Pts 94.
   - Gain bubbles: +5 with +1 above it over Speed, +7 with +1 above it over Stamina, +18 with +3 above it over Guts, +7 with +3 above it over the Wit/Skill Pts gap.
   - Failure badge "Failure 0%"; discipline buttons all "Lvl 3", Guts selected (raised, chevrons).
   - Scheduled Races: "Senior Year" tab active; "Early May" highlighted yellow; "Takarazuka Kinen" (Goal, Late Jun); "Kyoto Daishoten" (Scheduled, Early Oct); Reset and My Agendas.
5. **Primary action:** Tap a discipline button.
6. **Secondary actions:** Back, Skip, Quick, Details, calendar controls, rail navigation.
7. **Layout hierarchy:** Same shell as frame 001811 (header stack, goal banner, Energy row, facility banners, art, support rail, stat band, Failure badge, five buttons; right panel; nav rail). Refer to that note for the sketch.
8. **Visual hierarchy:** The +18 bubble pair first. The rainbow-ringed support card second (the only prismatic element). The stat band third.
9. **What carries state:** A support card's bond ceiling shows twice: the "MAX" label on the gauge and a coloured ring on the portrait; the Skill Points value drops as skills are bought (94 here against 139 in frame 133322); the calendar's year tab switches the whole grid.
10. **Absences:** No explanation of the ring versus the MAX label; no per-card attribution for the +3 bonus bubbles; no indication of how many turns remain in the year beyond the chip.
11. **What this screen teaches the app:** A single relationship carries two independent visual channels (gauge label and portrait ring), so a tool that models bond must store at least two facts per card, not one progress value.

---

## Screenshot 2026-07-18 135745

Frame: `docs/game-screenshots/Screenshot 2026-07-18 135745.png`, 1920×1080. Read 2026-10-03 by the Phase B extraction pass.

1. **Category:** training
2. **Screen name:** "Training" (top-left chip). Right panel: "Scheduled Races".
3. **Screen purpose:** The player picks training on the last turn before the scenario's deciding phase, with Guts at level 4 selected.
4. **Information presented:**
   - Turn chip "1 turn(s) left"; date strip "Senior Year Early Jun"; blue chip "1 turn(s) Until the Unity Cup".
   - Goal banner "Place top 3 in Takarazuka Kinen", orange "Entry criteria met!", Details.
   - Energy gauge about one third filled, "GREAT" mood pill.
   - Pink banner pair: "Guts Lvl 4" (megaphone icon) and "Stair Dash".
   - Background: a shrine courtyard with stone lion-dog statue and shimenawa rope.
   - Support card rail: six avatars; one with a "MAX" gauge label and an iridescent portrait ring; icons include book, heart, flame, globe.
   - Stat band: Speed 518/1325 (C), Stamina 536/1322 (C), Power 474/1358 (C), Guts 539/1308 (C), Wit 321/1800 (D), Skill Pts 129.
   - Gain bubbles: +7 with +2 above it over Speed, +7 with +2 above it over Stamina, +21 with +4 above it over Guts, +9 with +4 above it over the Wit/Skill Pts gap.
   - Failure badge: "Failure 5%".
   - Discipline buttons: Speed Lvl 3, Stamina Lvl 3, Power Lvl 3, Guts Lvl 4 (selected, raised, chevrons, pink), Wit Lvl 3.
   - Scheduled Races: "Senior Year" active; Early May and Late May greyed; "Early Jun" highlighted; "Takarazuka Kinen" (Goal, Late Jun); "Kyoto Daishoten" (Scheduled, Early Oct); "Arima Kinen" (Goal, Late Dec).
5. **Primary action:** Tap a discipline button.
6. **Secondary actions:** Back, Skip, Quick, Details, calendar controls, rail navigation.
7. **Layout hierarchy:** Same shell as frame 001811. Refer to that note for the sketch.
8. **Visual hierarchy:** The +21 bubble first (largest numeral in the corpus). The pink "Failure 5%" badge second, because it breaks the zero pattern of every other training frame. The Lvl 4 button third.
9. **What carries state:** Facility level is "Lvl N" on both the banner and the button, and a higher level re-tints the button and banner (pink at Lvl 4 versus orange at Lvl 3); failure risk is a percentage badge that turns pink when non-zero; past calendar cells are greyed; the activity name and background change together.
10. **Absences:** No explanation of what the 5% failure entails; no energy cost; no indication of why the shrine facility is available only at this point.
11. **What this screen teaches the app:** The training surface has a risk channel that stays at zero for most of a run and appears late, so a tool must render a failure probability as a first-class field rather than an afterthought.

---

## Screenshot 2026-07-18 135751

Frame: `docs/game-screenshots/Screenshot 2026-07-18 135751.png`, 1920×1080. Read 2026-10-03 by the Phase B extraction pass.

**Obstruction:** a Snipping Tool notification covers the lower right of the Scheduled Races panel and the nav rail's lower half.

1. **Category:** training
2. **Screen name:** "Training" (top-left chip). Right panel: "Scheduled Races".
3. **Screen purpose:** The player compares the same turn's options with Speed selected.
4. **Information presented:**
   - Turn chip "1 turn(s) left"; date strip "Senior Year Early Jun"; blue chip "1 turn(s) Until the Unity Cup".
   - Goal banner "Place top 3 in Takarazuka Kinen", orange "Entry criteria met!", Details.
   - Energy gauge about one third filled, "GREAT" mood pill.
   - Orange banner pair: "Speed Lvl 3" (shoe icon) and "Exercise Bike"; gym background.
   - Support card rail: three avatars (heart, book, flame icons).
   - Stat band: identical to frame 135745 (Speed 518/1325 C, Stamina 536/1322 C, Power 474/1358 C, Guts 539/1308 C, Wit 321/1800 D, Skill Pts 129).
   - Gain bubbles: +15 with +2 above it over Speed, +6 over the Power column, +6 with +1 above it over the Wit/Skill Pts gap.
   - Failure badge: "Failure 3%".
   - Discipline buttons: Speed Lvl 3 (selected, raised, chevrons), Stamina Lvl 3, Power Lvl 3, Guts Lvl 4 (still pink though unselected), Wit Lvl 3.
   - Scheduled Races: "Senior Year" active, Early May and Late May greyed, "Early Jun" highlighted, "Takarazuka Kinen" (Goal, Late Jun), "Kyoto Daishoten" (Scheduled, Early Oct), "Arima Kinen" (Goal, Late Dec).
5. **Primary action:** Tap a discipline button.
6. **Secondary actions:** Back, Skip, Quick, Details, calendar controls, rail navigation.
7. **Layout hierarchy:** Same shell as frame 135745. Refer to that note for the sketch.
8. **Visual hierarchy:** The +15 bubble first. The pink unselected Guts button second (it is the only non-orange button in the row). The blue "Failure 3%" badge third.
9. **What carries state:** Failure risk is per discipline (5% on Guts here, 3% on Speed); the button tint tracks facility level rather than selection, since the Lvl 4 button keeps its pink while unselected; the rail membership again follows the selection.
10. **Absences:** No legend for the level tints; no energy cost per discipline; no explanation of why Speed gains more here than on the previous turn's frame.
11. **What this screen teaches the app:** Selection and level are independent colour channels on the same control, so a faithful component needs two state props per button, not one.

---

## Screenshot 2026-07-18 135756

Frame: `docs/game-screenshots/Screenshot 2026-07-18 135756.png`, 1920×1080. Read 2026-10-03 by the Phase B extraction pass. Fourth discipline of the same turn as frames 135745 and 135751; this note carries only the differences.

**Obstruction:** a Snipping Tool notification covers the lower right of the Scheduled Races panel and the nav rail's lower half.

1. **Category:** training
2. **Screen name:** "Training" (top-left chip). Right panel: "Scheduled Races".
3. **Screen purpose:** The player previews Stamina training on the same turn.
4. **Information presented:**
   - Header, goal banner, Energy row, stat band and Scheduled Races panel are unchanged from frame 135745.
   - Orange banner pair: "Stamina Lvl 3" (heart icon) and "Backstroke"; background is an indoor pool.
   - Support card rail: two avatars only.
   - Gain bubbles: +13 over Stamina, +9 over the Power/Guts area, +5 over the Wit/Skill Pts gap.
   - Failure badge: "Failure 1%".
   - Discipline buttons: Stamina Lvl 3 selected (raised, chevrons); Guts Lvl 4 still pink while unselected; the rest orange.
5. **Primary action:** Tap a discipline button.
6. **Secondary actions:** Back, Skip, Quick, Details, calendar controls, rail navigation.
7. **Layout hierarchy:** Same shell as frame 135745. Refer to that note for the sketch.
8. **Visual hierarchy:** The +13 bubble first, the pool scene second, the "Failure 1%" badge third.
9. **What carries state:** This discipline's preview is the leanest in the set: two support cards and a 1% failure chance, against six cards and 5% on Guts and three cards and 3% on Speed on the same turn.
10. **Absences:** No stated reason why the rail shrinks per discipline; no energy cost; no breakdown of the +9 secondary gain.
11. **What this screen teaches the app:** Per-discipline previews differ in both participant count and risk, so a run record needs the discipline, the participant set and the failure chance together to be reproducible.

---

## Screenshot 2026-07-18 135801

Frame: `docs/game-screenshots/Screenshot 2026-07-18 135801.png`, 1920×1080. Read 2026-10-03 by the Phase B extraction pass. Fifth discipline of the same turn as frames 135745, 135751 and 135756; this note carries only the differences.

1. **Category:** training
2. **Screen name:** "Training" (top-left chip). Right panel: "Scheduled Races".
3. **Screen purpose:** The player previews Power training on the same turn.
4. **Information presented:**
   - Header, goal banner, Energy row, stat band and Scheduled Races panel unchanged from frame 135745.
   - Orange banner pair: "Power Lvl 3" (dumbbell icon) and "Sit-Ups"; gym background.
   - Support card rail: six avatars, one carrying a "MAX" gauge label with a flame icon; several carry globe icons.
   - Gain bubbles: +6 with +3 above it over the Stamina column, +17 with +6 above it over Power, +6 with +5 above it over the Wit/Skill Pts gap.
   - Failure badge: "Failure 2%".
   - Discipline buttons: Power Lvl 3 selected; Guts Lvl 4 pink and unselected; the rest orange.
5. **Primary action:** Tap a discipline button.
6. **Secondary actions:** Back, Skip, Quick, Details, calendar controls, rail navigation.
7. **Layout hierarchy:** Same shell as frame 135745. Refer to that note for the sketch.
8. **Visual hierarchy:** The +17 bubble first, the six-card rail second (the fullest of the five previews on this turn), the "Failure 2%" badge third.
9. **What carries state:** Across the four disciplines captured on this turn the failure badge reads 5% (Guts), 3% (Speed), 1% (Stamina) and 2% (Power), and the rail holds six, three, two and six cards, so both channels move with the discipline and neither is a global turn property. Wit was not captured.
10. **Absences:** No energy cost per discipline; no attribution of the +6 bonus to specific cards; no summary comparing the five previews.
11. **What this screen teaches the app:** A turn's preview is a per-discipline record of participants, gains and risk, so a tool that wants to reproduce a run must store the preview the player acted on, not just the discipline name.

---

