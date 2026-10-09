# Race Entry screenshots (merged)

10 frame notes, merged from individual `Screenshot *.md` files.

Each section preserves the original 11-field annotation format.

## Screenshot 2026-07-14 025344

Frame: `docs/game-screenshots/Screenshot 2026-07-14 025344.png`, 1920×1080. Read 2026-10-03 by the Phase B extraction pass.

1. **Category:** race-entry
2. **Screen name:** "Predictions" (modal heading). Background: the race entry screen with the "Scheduled Races" panel.
3. **Screen purpose:** The player reads the prediction for a G1 sprint race before entering.
4. **Information presented:**
   - Modal: green "Predictions" banner; portrait and name "Curren Chan"; race plate "G1 Sprinters Stakes", "Nakayama Turf 1200m (Sprint) Right / Outer".
   - Aptitude row: Speed double-circle, Stamina cross, Power double-circle, Guts cross, Wit circle.
   - Speech bubble: "Your trainee seems pumped up and ready for a challenge. I'd say she's definitely a contender." with "contender" colour-highlighted.
   - Close button.
   - Background, left: a "Race Day" banner, the goal strip ("Place top 1 in Sprinters S…" with an orange sub-line), Energy gauge with a mood pill, Predictions and Race buttons, Back, Skip, Quick.
   - Background, right: Scheduled Races on the Classic Year tab with "Late Sep" highlighted and holding a race card; Reset and My Agendas.
   - Nav rail far right, with a pink notification dot on Menu.
5. **Primary action:** Close the modal and tap Race.
6. **Secondary actions:** Reopen Predictions; navigate the calendar.
7. **Layout hierarchy:** Same surface as frames 232527 and 234615: centred modal over the dimmed entry screen, calendar right, nav rail far right. Refer to those notes for the sketch.
8. **Visual hierarchy:** Modal first; the two cross marks second (they break the row's pattern); the Race Day banner behind third.
9. **What carries state:** As the other Predictions frames: mark shapes per stat, prose verdict keyed to the marks, race grade chip, plate text. The verdict wording differs from the other two frames and drops the "top" qualifier, matching the two crosses in the row.
10. **Absences:** No numeric probability; no explanation of what "contender" versus "top contender" costs the player in information; no rival field shown.
11. **What this screen teaches the app:** The verdict sentence has at least three gradations across the three Predictions frames captured ("definitely a contender", "might just be a top contender", "has what it takes to be a top contender"), so a faithful tool must treat the verdict as a small enumerated set rather than free text.

---

## Screenshot 2026-07-17 232527

Frame: `docs/game-screenshots/Screenshot 2026-07-17 232527.png`, 1920×1080. Read 2026-10-03 by the Phase B extraction pass.

1. **Category:** race-entry
2. **Screen name:** "Predictions" (modal heading). The blurred background is the race entry screen with a "Scheduled Races" panel on the right.
3. **Screen purpose:** The player reads the game's prediction of how the trainee will place in the race they are about to enter.
4. **Information presented:**
   - Modal: green heading banner "Predictions"; the trainee's portrait and the name "Rice Shower"; race plate "G2 Spring Stakes", "Nakayama Turf 1800m (Mile) Right / Inner"; a five-column aptitude row headed Speed, Stamina, Power, Guts, Wit with a double-circle mark under the first four and a triangle under Wit; a speech bubble "Your trainee has what it takes to be a top contender."; a Close button.
   - Background, left: the entry screen's goal strip ("Place top 5th in Spring S…" with an orange line beneath), Energy gauge with mood pill, Predictions and Race buttons, Back, Skip and Quick controls.
   - Background, right: Scheduled Races grid on the Classic Year tab with several race cards placed and at least two Goal pennants, Reset and My Agendas buttons.
5. **Primary action:** Close the modal and proceed to the Race button.
6. **Secondary actions:** Reopen Predictions from the entry screen; navigate the calendar behind.
7. **Layout hierarchy:** The modal is centred and saturates the left half: heading banner, portrait, name, race plate, aptitude row, advisor speech bubble, Close. The entry screen sits behind at reduced emphasis, its Race button and calendar still legible. The nav rail is present at far right.
8. **Visual hierarchy:** The modal first (white card on dimmed background, green banner). The aptitude row second (green header band, large marks). The Race button third (green on white, behind the modal). The calendar reads as context.
9. **What carries state:** Aptitude grade is a mark shape (double-circle, circle, triangle, cross by the game's convention, though only double-circle and triangle appear here); the prediction verdict is prose with the key phrase colour-highlighted; race grade is a letter chip (G2) beside the race name; surface, distance, direction and turn are spelled out in the race plate; scheduled vs open calendar cells use card art versus grey plus-slots.
10. **Absences:** No numeric win probability; no percentile or odds figure; no comparison against named rivals; the aptitude row shows marks but no underlying letter grades on this modal.
11. **What this screen teaches the app:** The game communicates expected performance as a ranked verdict over per-stat aptitude marks, translating the same data a numbers-first app would show as odds into a single prose sentence a player can act on.

---

## Screenshot 2026-07-17 234444

Frame: `docs/game-screenshots/Screenshot 2026-07-17 234444.png`, 1920×1080. Read 2026-10-03 by the Phase B extraction pass.

1. **Category:** race-entry
2. **Screen name:** "Career" (top-left screen chip). Right panel: "Log".
3. **Screen purpose:** On the race's scheduled turn, the player chooses between racing now and spending the turn on skills.
4. **Information presented:**
   - Header: a blue "GOAL" pennant where the turn-number chip sits on other turns; date strip "Classic Year Late May"; blue chip "2 turn(s) Until the Unity Cup".
   - Goal banner: "Place top 5 in Japanese Derby", orange sub-line "Entry criteria met!", Details button.
   - Energy gauge about one quarter filled with a "GREAT" mood pill (pink, up-right arrow).
   - Team laurel: "Blue Bloom" D rank, "Starting Out".
   - Hint bubble: "It's the day of the race your trainee has been working towards. All that's left is to see how it turns out."
   - Performance chip: "Gold" with an info button; a "Full Stats" button.
   - Stat band: Speed 337/1321 (D), Stamina 391/1319 (D+), Power 306/1334 (D), Guts 412/1307 (C), Wit 145/1800 (F), Skill Pts 308.
   - Action buttons: "Skills" (teal, with book and paper icons) and "Race!", enlarged, pink, wearing a red "Race Day" ribbon and three numbered ticket icons.
   - Log panel: dialogue cards ("Rice Shower" twice), an outcome card "Energy went down by 5. / Guts went up by 9. / Skill Pts went up by 43.", a green collapsible phase bar "The Unity Cup Is Almost Here!" with an up chevron and a scenario logo, then three "Tazuna Hayakawa" cards ("Hello, Trainer!", "The Unity Cup is almost here!", "Make sure to get your training in so you're prepared for it!").
   - Skip and Quick buttons; menu button. Nav rail: Log active.
5. **Primary action:** Tap Race! to enter the scheduled race.
6. **Secondary actions:** Open Skills; Skip or Quick; scroll or collapse the Log; rail navigation.
7. **Layout hierarchy:** Left half: header stack (GOAL pennant, date, Unity Cup chip), goal banner, Energy row, team laurel left edge, hint bubble top-centre, trainee art centre with Gold chip and Full Stats floating, stat band, then the two action buttons with Race! visually dominant. Right half: the Log, dialogue cards with a green phase divider. Nav rail far right.
8. **Visual hierarchy:** The Race! button first (pink, enlarged, ribboned, glowing). The stat band second. The hint bubble third. The Log is context at lower contrast; the phase bar interrupts its scroll with saturated green.
9. **What carries state:** Race day is encoded by the GOAL pennant replacing the turn chip, the Race Day ribbon, and the reduction of the action set to two buttons; performance tier is the Gold chip; the log's phase bar groups a span of turns under a scenario header and is collapsible; outcome deltas are sentences with direction words colour-coded (down blue, up orange).
10. **Absences:** No race details (distance, grade, surface) on this surface; no prediction access without entering; no way to skip the race from this screen; the outcome card carries no turn identifier.
11. **What this screen teaches the app:** A scheduled race changes the action vocabulary itself (two buttons, one enlarged) rather than adding a badge to the normal menu, so the day's constraint is structural, not advisory.

---

## Screenshot 2026-07-17 234615

Frame: `docs/game-screenshots/Screenshot 2026-07-17 234615.png`, 1920×1080. Read 2026-10-03 by the Phase B extraction pass.

1. **Category:** race-entry
2. **Screen name:** "Predictions" (modal heading). Background: the race entry screen and Log panel, blurred.
3. **Screen purpose:** The player reads the prediction for the Japanese Derby before entering it.
4. **Information presented:**
   - Modal: green "Predictions" banner; the trainee's portrait and the name "Rice Shower"; race plate "G1 Tokyo Yushun (Japanese Derby)", "Tokyo Turf 2400m (Medium) Left".
   - Aptitude row: Speed double-circle, Stamina double-circle, Power circle, Guts double-circle, Wit cross.
   - Speech bubble: "Your trainee's form is good. She might just be a top contender." with "top contender" colour-highlighted.
   - Close button. Background: goal strip, Energy gauge with mood pill, Predictions and Race buttons, Back, Skip, Quick; Log panel with the same dialogue and phase-bar content as frame 234444.
5. **Primary action:** Close the modal and proceed to Race.
6. **Secondary actions:** Reopen Predictions; navigate the Log.
7. **Layout hierarchy:** Centred modal (banner, portrait, name, race plate, aptitude row, speech bubble, Close) over the dimmed entry screen. Same structure as frame 232527; refer to that note for the sketch.
8. **Visual hierarchy:** The modal card first. The aptitude mark row second; the Wit cross draws by being the only negative mark in the row. The highlighted "top contender" phrase third.
9. **What carries state:** Aptitude marks use a four-level shape vocabulary visible across the two Predictions frames: double-circle, circle, triangle (232527), cross (this frame). The verdict prose keys its phrase to the marks; race grade is the G1 chip; direction and turn are in the plate.
10. **Absences:** No numeric odds; no rival comparison; no mood or energy influence shown despite the verdict wording referencing form; the mark row carries no legend.
11. **What this screen teaches the app:** The prediction modulates its verdict text with the aptitude pattern, and a single cross-shaped mark is enough to carry the worst rating, so the mark vocabulary must include a negative end, not just positive ranks.

---

## Screenshot 2026-07-17 234912

Frame: `docs/game-screenshots/Screenshot 2026-07-17 234912.png`, 1920×1080. Read 2026-10-03 by the Phase B extraction pass.

1. **Category:** race-entry
2. **Screen name:** No title in frame. Left: the victory celebration art with a gold "1st" laurel. Right: "Career Profile" panel.
3. **Screen purpose:** The player watches the win presentation immediately after the race, with the run's standing data alongside.
4. **Information presented:**
   - Full-bleed victory art of the trainee with a gold "1st" laurel and crown.
   - Bottom controls on the art: "Photo" (camera icon), a skip (fast-forward) button, a menu button.
   - Career Profile panel: "Trainee" with the card "[Rosy Dreams] Rice Shower", "Scenario Link" badge, "Potential Lv12", three of five stars; "Legacy Umamusume" with "Legacy 1" (B+ RANK, B+ RANK, A RANK portraits linked by lines) and "Legacy 2" (U° RANK, U° RANK and one "Guest" tagged portrait); "Support Cards" row of six SSR cards with "Lvl 35", "Lvl 30", "Lvl 30", "Lvl 35" (with a "Scenario Link" banner), "Lvl 30", "Lvl 50" (diamond pips and a pink "Friends" banner), and a "Perks" button.
   - Nav rail: Jukebox, Sparks, Log, Career Profile (active, green), Agenda, Item Request, Menu (NEW).
5. **Primary action:** Let the presentation finish or skip it.
6. **Secondary actions:** Take a Photo; open Perks; rail navigation.
7. **Layout hierarchy:** Left two thirds: the art with three small circular controls at the bottom. Right: the Career Profile card with three green section banners stacked (Trainee, Legacy Umamusume, Support Cards). Nav rail far right. No Next button is visible in this frame, unlike the result screen.
8. **Visual hierarchy:** The gold laurel first. The trainee art second. The profile panel third; within it, the Support Cards row's two badged cards draw before the rest.
9. **What carries state:** Placement is the laurel art; scenario membership is the "Scenario Link" badge on the trainee card and on one support card; deck role is the pink "Friends" banner on the sixth card; card level is "Lvl N" plus diamond pips; legacy quality is a letter-rank medallion per portrait; the guest slot is a word tag.
10. **Absences:** No race name, time or margins on this surface (those arrive on the result screen); no fan count; no confirmation control other than skip.
11. **What this screen teaches the app:** The celebration moment and the profile summary share one screen, so the emotional peak is immediately legible against the concrete state (deck, legacies) that produced it.

---

## Screenshot 2026-07-17 234918

Frame: `docs/game-screenshots/Screenshot 2026-07-17 234918.png`, 1920×1080. Read 2026-10-03 by the Phase B extraction pass.

**Obstruction:** a Snipping Tool notification covers part of the nav rail's lower half.

1. **Category:** race-entry
2. **Screen name:** Race result over "Career Profile". Race plate: "G1 Tokyo Yushun (Japanese Derby)".
3. **Screen purpose:** The player reviews the Derby result before continuing.
4. **Information presented:**
   - Result art panel: "1st" laurel over the winner; Replay button; speech bubble "Congratulations! Keep up the good work and get more wins together!"; a trophy icon.
   - Race plate: "G1 Tokyo Yushun (Japanese Derby)", "Tokyo Turf 2400m (Medium) Left", "Firm" with cloud and sun icons.
   - Result rows: 1st, gate 9, "Rice Shower", "Pace", "2:22.2", "No. 1 Fav", row highlighted yellow; 2nd, gate 3, "Eishin Flash", "Late", "3 L", "No. 4 Fav"; 3rd, gate 12, "Mihono Bourbon", "Front", "1 1/2 L", "No. 2 Fav"; a 4th row cut by the panel edge, "Late".
   - Career Profile panel: identical structure to frame 155757 (Trainee card "[Rosy Dreams] Rice Shower" with Scenario Link and Potential Lv12, three stars; Legacy 1 B+/B+/A; Legacy 2 with Guest; six SSR support cards, Lvl 35/30/30/35/30/50, Scenario Link and Friends banners, Perks button).
   - Green "Next" button; nav rail (lower part behind the obstruction).
5. **Primary action:** Tap Next.
6. **Secondary actions:** Replay; Perks; rail navigation.
7. **Layout hierarchy:** Same surface as frame 155757: result art with plate and rows on the left, Career Profile card on the right, Next centred at the bottom. Refer to that note for the sketch.
8. **Visual hierarchy:** Gold laurel, then the yellow winner row, then Next. Same ordering as 155757.
9. **What carries state:** As 155757, plus: gate numbers are small square chips beside each runner's name; the fraction margin form "1 1/2 L" appears alongside whole-length margins; weather icons accompany the going word ("Firm" with cloud and sun here, sun and moon on 155757).
10. **Absences:** As 155757: no fan or prize tally, no time comparison beyond margins, no scrollbar affordance beyond the cut row.
11. **What this screen teaches the app:** The result surface is instance-independent: the same anatomy carries any race, with only the plate, rows and weather icons varying, so a faithful layout is a template with three data slots.

---

## Screenshot 2026-07-17 235511

Frame: `docs/game-screenshots/Screenshot 2026-07-17 235511.png`, 1920×1080. Read 2026-10-03 by the Phase B extraction pass.

1. **Category:** race-entry
2. **Screen name:** Race result over "Career Profile". Race plate: "Unity Cup Preseason Round 2".
3. **Screen purpose:** The player reviews a team race result, where both the win and the own-team members' placements are shown.
4. **Information presented:**
   - Result art panel: a "WIN" ribbon banner over the art (not the individual "1st" laurel); Replay button.
   - Race plate: "Unity Cup Preseason Round 2", "Fukushima Turf 2600m (Long) Right", "Firm" with sun and moon icons.
   - Result rows: 1st, gate 3, "Rice Shower" with team line "Blue Bloom" in blue, "Pace", "2:37.2", "No. 2 Fav", the row highlighted yellow; 2nd, gate 6, "Flamenco Step", team "Team Lucky Number", "Late", "7 L", "No. 4 Fav"; 3rd, gate 12, "Eishin Flash", team "Blue Bloom", "Late", "Head", "2:38.3", "No. 1 Fav", also highlighted yellow; 4th, "Seiun Sky", "Front", cut by the panel edge.
   - Career Profile panel: identical structure to frame 155757.
   - Green "Next" button; nav rail.
5. **Primary action:** Tap Next.
6. **Secondary actions:** Replay; Perks; rail navigation.
7. **Layout hierarchy:** Same surface as frame 155757. Refer to that note for the sketch.
8. **Visual hierarchy:** The "WIN" ribbon, then the two yellow-highlighted rows, then Next. The team-name lines read as a second layer inside each row.
9. **What carries state:** A team race replaces the individual laurel with a "WIN" banner; own-team membership is both the blue team-name line and the yellow row highlight, which appears on every own-team row rather than only the winner; margins and times as in 155757.
10. **Absences:** No team points or score tally; no round standings after this round; the 4th row's team line is cut off.
11. **What this screen teaches the app:** In a team race the result unit shifts from one runner to a team of entries, and highlighting plus a team line are the two channels that carry that shift.

---

## Screenshot 2026-07-18 000919

Frame: `docs/game-screenshots/Screenshot 2026-07-18 000919.png`, 1920×1080. Read 2026-10-03 by the Phase B extraction pass.

1. **Category:** race-entry
2. **Screen name:** No title. The surface is the live race board: "Kyoto 11 R" with a red "FINAL" panel.
3. **Screen purpose:** The player watches the race finish on the trackside board while commentary runs.
4. **Information presented:**
   - Board header: "Kyoto", "11", "R"; a red "FINAL" lamp.
   - Placement column: roman numerals I to V in blue discs, each beside an amber dot-matrix gate number (7, 4, 1, 16, 10), each pair joined by a chevron to a margin cell ("DST", "3", "2 1/2", "1/2").
   - "Time" row: "3.02.4".
   - Going row: "Turf Firm" and "Dirt Firm".
   - Left: the trainee's victory art waving, a small trainee avatar chip.
   - Caption line: "Crown! We've witnessed the birth of a champion here today!"
   - Commentary line: "Commentary:" (pink label) then "In second is Symboli Rudolf. In third place is Seiun Sky."
   - Controls: an "Off" pill with a fast-forward icon (bottom left), a skip button and a menu button (bottom right).
5. **Primary action:** Watch the finish, then continue.
6. **Secondary actions:** Toggle the fast-forward control; open the menu.
7. **Layout hierarchy:** The frame is split: left half is the character presentation over the track with caption and commentary text across the bottom; right half is the physical board, header lamp at top, five placement rows down, then the Time and going rows at its foot.
8. **Visual hierarchy:** The red FINAL lamp first (the only saturated red). The amber numerals second. The victory art third. Commentary text is small and last.
9. **What carries state:** Finish state is the lamp; placement is the roman numeral column; margins are per-row dot-matrix cells with "DST" for the leader; the race number and venue are header cells; commentary names the placers in prose; the Off pill is a toggle.
10. **Absences:** No odds or favourite markers on the board; no per-runner names on the board itself (only gate numbers); no progress or distance-remaining indicator; no lap or section times.
11. **What this screen teaches the app:** This is the client's one dark surface, amber dot-matrix on charcoal, and it carries placement data with no identity beyond gate numbers, so the mapping from gate to runner lives in the commentary layer, not the board.

---

## Screenshot 2026-07-18 004353

Frame: `docs/game-screenshots/Screenshot 2026-07-18 004353.png`, 1920×1080. Read 2026-10-03 by the Phase B extraction pass.

1. **Category:** race-entry
2. **Screen name:** Race result over "Career Profile". Race plate: "G2 Nikkei Shinshun Hai".
3. **Screen purpose:** The player reviews a race the trainee lost, seeing the full finishing order with the trainee's row marked.
4. **Information presented:**
   - Result art: a silver "2nd" laurel over the trainee; Replay button; a trophy icon; speech bubble "Victory was this close to being yours, but the results will serve as a good experience for the next race."
   - Race plate: "G2 Nikkei Shinshun Hai", "Kyoto Turf 2400m (Medium) Right / Outer", "Firm" with cloud and sun icons.
   - Result rows: 1st, gate 10, "Tosen Jordan", "Pace", "2:21.9", "No. 3 Fav"; 2nd, gate 4, "Rice Shower", "Pace", "1 3/4 L", "No. 1 Fav", row highlighted yellow; 3rd, gate 6, "Mejiro Ryan", "Pace", "1/2 L", "No. 5 Fav"; a 4th row cut at the panel edge, "Pace".
   - Career Profile panel: same structure as frame 155757, "Trainee" showing the card "[Rosy Dreams] Rice Shower".
   - Green "Next"; nav rail with Career Profile active.
5. **Primary action:** Tap Next.
6. **Secondary actions:** Replay; Perks; rail navigation.
7. **Layout hierarchy:** Same surface as frame 155757. Refer to that note for the sketch.
8. **Visual hierarchy:** The silver 2nd laurel first, then the yellow trainee row, then Next. The winner's gold laurel in the list reads below the art's placement because the art is about the trainee, not the race.
9. **What carries state:** The art laurel is the trainee's placement while the list laurels are the race order; the yellow row highlight marks the player's runner rather than the winner; the speech bubble's register changes with placement (congratulation on 155757, consolation here).
10. **Absences:** No indication of what the race awarded (fans, prize); no gap-to-leader summary in the plate; no mark on the winner's row.
11. **What this screen teaches the app:** A result surface has two subjects at once, the race order and the player's place in it, and it separates them by putting the player's placement in the art and the field in the list.

---

## Screenshot 2026-07-18 155757

Frame: `docs/game-screenshots/Screenshot 2026-07-18 155757.png`, 1920×1080. Read 2026-10-03 by the Phase B extraction pass.

1. **Category:** race-entry
2. **Screen name:** Race result (no title in frame; the left half is the result view) over "Career Profile" (green banner, right half). The race name plate reads "G1 Takarazuka Kinen".
3. **Screen purpose:** The player reviews the outcome of the race that just ran, then continues the career.
4. **Information presented:**
   - Result art panel: "1st" laurel over the winner; a Replay button; a speech bubble "Congratulations! Keep up the good work and get more wins together!".
   - Race name plate: "G1 Takarazuka Kinen", "Kyoto Turf 2200m (Medium) Right / Outer", "Firm" with sun and moon icons.
   - Result rows: 1st, gate 1, the trainee, "Pace", time "2:11.0", "No. 1 Fav"; 2nd, gate 17, an opponent, "End", margin "3 L", "No. 8 Fav"; 3rd, gate 8, an opponent, "Late", margin "Neck", "No. 7 Fav"; a 4th row partially cut by the panel edge, "Late".
   - Career Profile panel: "Trainee" section with the character card "[Rosy Dreams] Rice Shower", "Scenario Link" badge, "Potential Lv12", three of five stars; "Legacy Umamusume" with "Legacy 1" (ranks B+, B+, A) and "Legacy 2" (two U-grade ranks and one marked "Guest"); "Support Cards" row of six SSR cards with levels 35, 30, 30, 35, 30, 50, one carrying a "Scenario Link" badge and one a pink "Friends" badge, and a "Perks" button.
   - Right nav rail: Jukebox, Sparks, Log, Career Profile (active, green), Agenda, Item Request, Menu (marked NEW).
   - Primary button: "Next".
5. **Primary action:** Tap Next to leave the result and continue the run.
6. **Secondary actions:** Replay the race; open Perks on the support card row; navigate to Jukebox, Sparks, Log, Career Profile, Agenda, Item Request or Menu from the rail.
7. **Layout hierarchy:** The frame is two surfaces. Left: the result, a full-bleed art panel with the race name plate at its bottom, the scrollable result rows below it, and a wide green Next button centred at the bottom. Right: the Career Profile card with stacked green section banners (Trainee, Legacy Umamusume, Support Cards) on a white card. Far right: the vertical nav rail with icon-over-label tiles. The result overlays the same app shell the profile lives in.
8. **Visual hierarchy:** The gold "1st" laurel first (size plus saturation on dark art). The Next button second (bright green on white). The result rows third (yellow highlight on the winner row). The Career Profile panel reads as context, not action: smaller type, thin section banners.
9. **What carries state:** Placement is a number plus laurel art (gold 1st, silver 2nd, bronze 3rd); running style is a word chip (Pace, End, Late); margins are L lengths or body-part words (3 L, Neck); favourite status is "No. N Fav"; time is a fixed-format readout; card rarity is the SSR badge with Lvl; Scenario Link and Friends are badge overlays on specific cards; potential is "Potential LvN"; legacy rank is a letter-grade medallion per legacy member.
10. **Absences:** No fan count or reward tally for the win; no payout or prize readout; no time comparison against rivals other than margins; no per-section expand/collapse on the profile; the fourth and lower result rows are present but cut off, with no visible scrollbar affordance other than the panel's own.
11. **What this screen teaches the app:** A race outcome is presented as placement plus per-runner mechanics data (style, margin, time, favouritism) while the run's standing data (trainee card, legacies, support deck) stays on screen beside it, so the result never has to be correlated from memory.

---

