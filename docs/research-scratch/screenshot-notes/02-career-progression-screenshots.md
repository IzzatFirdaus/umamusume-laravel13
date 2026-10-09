# Career Progression screenshots (merged)

9 frame notes, merged from individual `Screenshot *.md` files.

Each section preserves the original 11-field annotation format.

## Screenshot 2026-07-17 235229

Frame: `docs/game-screenshots/Screenshot 2026-07-17 235229.png`, 1920×1080. Read 2026-10-03 by the Phase B extraction pass.

**Obstruction:** a Snipping Tool notification covers the lower right of the Scheduled Races panel and part of the nav rail.

1. **Category:** career-progression
2. **Screen name:** "Team Showdown" (top-left screen chip), "Select Opponent" heading. Right panel: "Scheduled Races".
3. **Screen purpose:** The player picks which of three opponent teams to challenge.
4. **Information presented:**
   - Team status bar: D-rank laurel, "Blue Bloom", "Starting Out" tag, rank "22nd", info button.
   - Three opponent cards: "Rank 19", blue D laurel, three portraits, "Team Lucky Number" on a green banner, "Win to become rank 14."; "Rank 23", purple E laurel, two portraits, "Logical Milk Tea" on a blue banner, "Win to become rank 17."; "Rank 29", purple F laurel, two portraits, "KIAI Command" on a purple banner, "Win to become rank 20."; the third card is selected (green corner brackets).
   - "Select Opponent" confirm button; Back; Skip Off-style Skip button; Quick; menu button.
   - Scheduled Races: "Classic Year" active, "Late Jun" highlighted, "Kyoto Daisen" (Scheduled, Early Oct), "Kikuka Sho" (Goal, Late Oct), "Queen Elizabeth Cup" (Scheduled, Early Nov).
   - Nav rail: Agenda active.
5. **Primary action:** Confirm Select Opponent for the bracketed card.
6. **Secondary actions:** Choose a different card; Back, Skip, Quick; calendar controls; rail navigation.
7. **Layout hierarchy:** Same surface as frame 160543: status bar, three stacked opponent cards, confirm button, calendar right, nav rail. Refer to that note for the sketch.
8. **Visual hierarchy:** The bracketed selected card, then the rank numerals, then the two Goal pennants on the calendar. Same ordering as 160543.
9. **What carries state:** As 160543, plus: laurel colour varies below D (purple for E and F here); the team name banner colour varies per opponent (green, blue, purple); the player's tag word differs with progress ("Starting Out" here versus "Up-and-Coming" on 160543).
10. **Absences:** As 160543: no opponent strength numbers, no win-chance estimate, no reward beyond the rank line.
11. **What this screen teaches the app:** The opponent ladder is continuous from F to B and beyond, and the reachable-rank reward line scales with the gap, so rank distance is the only difficulty signal the surface gives.

---

## Screenshot 2026-07-17 235529

Frame: `docs/game-screenshots/Screenshot 2026-07-17 235529.png`, 1920×1080. Read 2026-10-03 by the Phase B extraction pass.

1. **Category:** career-progression
2. **Screen name:** "RACE FINISHED" (heading banner). Background: the Scheduled Races panel, dimmed.
3. **Screen purpose:** The player reads the outcome of a team match: five simultaneous races, each with per-member placements for both teams.
4. **Information presented:**
   - Heading: "RACE FINISHED" on a green ribbon banner.
   - Five race rows, each: "Race 1" through "Race 5", a race plate ("Fukushima Dirt 1700m (Mile) Right", "Fukushima Turf 2000m (Medium) Right", "Fukushima Turf 2600m (Long) Right", "Fukushima Turf 1200m (Sprint) Right", "Fukushima Turf 1800m (Mile) Right"), a pink "Spr" badge, a going word with weather icons ("Good" with sun, "Firm" with cloud or sun), then a verdict word (orange "WIN" or blue "LOSE"), the own team's three member cards with placement badges ("1st" plus two empty silhouette slots; "2nd 4th 15th"; "1st 3rd 5th"; "2nd 1st 12th"; "7th 2nd 12th"), a "VS" mark, and the opponent team's three cards with placements ("2nd 3rd" plus empty; "1st 11th 3rd"; "4th 9th 2nd"; "4th 3rd" plus empty; "1st 9th 3rd").
   - Green "Next" button. Nav rail behind the dim.
5. **Primary action:** Tap Next.
6. **Secondary actions:** None visible.
7. **Layout hierarchy:** Full-width heading ribbon, then five stacked match rows, each row a green-headed card with the two three-card teams facing across a VS. Next centred at the bottom.
8. **Visual hierarchy:** The WIN/LOSE words first (colour-coded verdicts). The gold "1st" badges second. The race plates third. Placement badges on greyscale silhouette slots read as absence.
9. **What carries state:** Match verdict is a colour-coded word per race; individual placement is a small badge overlaid on each member card; an unentered slot is a greyscale silhouette with no badge; the going and weather are word-plus-icon; the pink "Spr" badge marks a race property present on all five rows here.
10. **Absences:** No score or points total across the five races; no indication of how the match verdict maps to rank change; no expanded per-race detail; no times.
11. **What this screen teaches the app:** A multi-race match is summarized at the placement-badge level, so the aggregate outcome (rank change) is computed elsewhere and this surface shows only per-race, per-member raw results.

---

## Screenshot 2026-07-17 235902

Frame: `docs/game-screenshots/Screenshot 2026-07-17 235902.png`, 1920×1080. Read 2026-10-03 by the Phase B extraction pass.

1. **Category:** career-progression
2. **Screen name:** "Career" (top-left chip) with a "Trainee Event" ribbon; the right panel is "Choices".
3. **Screen purpose:** The player answers a trainee event, with the possible outcomes of each answer listed before committing.
4. **Information presented:**
   - Header: turn chip "5 turn(s) left"; date strip "Classic Year Early Aug"; blue chip "9 turn(s) Until the Unity Cup"; goal banner "Place top 3 in Kikuka Sho" with orange sub-line "Entry criteria met!" and Details; Energy gauge about one third filled with a "GREAT" mood pill.
   - Event ribbons: "Trainee Event" (pink) over the event title "Get Well Soon!".
   - Trainee art in an infirmary room, subdued expression.
   - Two choice banners with arrow caps: "Make her take a proper rest." (green cap) and "Encourage her to keep going." (yellow cap, the highlighted one).
   - Speech bubble: "But summer camp only comes around once a… and time is ticking. What to do…" with an "Effects" button.
   - Choices panel: the trainee card "[Rosy Dreams] Rice Shower" and "Full Stats"; stat band Speed 367/1321 (D+), Stamina 428/1319 (C), Power 373/1344 (D+), Guts 465/1307 (C), Wit 191/1800 (F+), Skill Pts 123.
   - Outcome preview for the first choice (partially scrolled): "Branch 1" with "Mood -1" and "Previously trained attribute -5"; "Branch 2" with "Mood -1", "Previously trained attribute -5", "Become Practice Poor".
   - Selected choice restated in a green banner: "Encourage her to keep going.", then its branches: "Branch 1" Mood -1, Previously trained attribute -10; "Branch 2" Mood -1, Previously trained attribute -10, Become Practice Poor; "Branch 3" "Become Practice Perfect ○".
   - Close button; Skip and Quick; menu button.
5. **Primary action:** Pick one of the two choice banners.
6. **Secondary actions:** Open Effects; Full Stats; goal Details; Skip or Quick.
7. **Layout hierarchy:** Left: header stack, goal banner, Energy row, event ribbon pair, event art centre, two stacked choice banners, speech bubble with Effects at the bottom. Right: the Choices card, trainee header, stat band, then a scrollable list of branch outcome cards grouped under the selected choice's green banner.
8. **Visual hierarchy:** The two choice banners first (full width, arrow caps, one colour-highlighted). The green selected-choice banner second. The branch cards third, each small but dense with signed numbers.
9. **What carries state:** The chosen option is the highlighted banner; each branch is a named card whose lines are signed deltas ("Mood -1", "Previously trained attribute -5" versus "-10") and a status change line ("Become Practice Poor", "Become Practice Perfect ○"); the branch count differs per choice (two versus three).
10. **Absences:** No probabilities attached to the branches; no indication of which branch is more likely; the delta magnitudes are shown without the resulting stat values; the first choice's branch list is cut at the panel top.
11. **What this screen teaches the app:** The game discloses an uncertain decision as a branch table with signed magnitudes but no probabilities, so a faithful tool must render outcome sets, not expected values.

---

## Screenshot 2026-07-18 002421

Frame: `docs/game-screenshots/Screenshot 2026-07-18 002421.png`, 1920×1080. Read 2026-10-03 by the Phase B extraction pass.

1. **Category:** career-progression
2. **Screen name:** "Career" (top-left chip) with a "Trainee Event" ribbon; the right panel is "Choices".
3. **Screen purpose:** The player answers a three-way trainee event with every option's outcome branches listed.
4. **Information presented:**
   - Header: turn chip "6 turn(s) left"; date strip "Classic Year Late Dec"; blue chip "Unity Cup Begins at End of Turn"; goal banner "Place top 3 in Nikkei Sho" with "Entry criteria met!" and Details; Energy gauge nearly full with a "NORMAL" mood pill (gold, right arrow).
   - Event ribbons: "Trainee Event" over "A Wish to the Goddesses".
   - Event art: the trainee at a fountain courtyard.
   - Three choice banners with arrow caps: "Put all your heart into one coin." (green), "How about a coin for each Goddess?" (yellow), "Try tossing in a whole bunch!" (pink).
   - Speech bubble: "That's such a big thing to ask for, though… so I wasn't sure how many coins to toss." with an "Effects" button.
   - Choices panel: trainee card "[Rosy Dreams] Rice Shower", "Full Stats"; stat band Speed 390/1321 (D+), Stamina 443/1319 (C), Power 401/1344 (C), Guts 468/1307 (C), Wit 208/1800 (E), Skill Pts 116. Then, per option, a green option banner followed by its branch cards: "Put all your heart into one coin." → "Branch 1: Mood +1, Random 1 attribute(s) +30", "Branch 2: Random 1 attribute(s) +15"; "How about a coin for each Goddess?" → the same two branches; "Try tossing in a whole bunch!" banner at the panel's bottom edge, its branches below the fold.
   - Close; Skip; Quick; menu.
5. **Primary action:** Tap one of the three choice banners.
6. **Secondary actions:** Effects; Full Stats; Details; Skip or Quick.
7. **Layout hierarchy:** Left: header stack, goal banner, Energy row, event ribbon pair, art, three stacked choice banners, speech bubble. Right: the Choices card, trainee header, stat band, then a repeating pattern of green option banner plus branch cards.
8. **Visual hierarchy:** The three arrow-capped choice banners first, each keyed to its own colour. The signed branch numbers second (+30 in orange). The stat band third.
9. **What carries state:** Option identity is the banner colour shared between the left choice and the right panel; each branch is a card of signed deltas, with "Mood +1" appearing on only some branches; the panel groups branches under their option rather than showing one option at a time.
10. **Absences:** No probabilities per branch; no indication of which branch is likelier; the third option's branches are not visible in this frame; "Random 1 attribute(s)" names no attribute.
11. **What this screen teaches the app:** The Choices panel is a comparison table, not a confirmation dialog: every option's full branch set is listed at once, so a tool that mirrors it must render option-by-branch structure with colour as the join key.

---

## Screenshot 2026-07-18 002432

Frame: `docs/game-screenshots/Screenshot 2026-07-18 002432.png`, 1920×1080. Read 2026-10-03 by the Phase B extraction pass. Scroll sibling of frame 002421 (same event, panel scrolled down); this note carries only what the earlier frame cannot show.

1. **Category:** career-progression
2. **Screen name:** "Career" with a "Trainee Event" ribbon ("A Wish to the Goddesses"); right panel "Choices".
3. **Screen purpose:** The player reads the remaining options' outcome branches by scrolling the Choices panel.
4. **Information presented:**
   - Left half unchanged from frame 002421: turn chip "6 turn(s) left", "Classic Year Late Dec", "Unity Cup Begins at End of Turn", goal "Place top 3 in Nikkei Sho" with "Entry criteria met!", Energy with "NORMAL" pill, the three choice banners, the speech bubble and Effects button.
   - Stat band unchanged: Speed 390/1321 (D+), Stamina 443/1319 (C), Power 401/1344 (C), Guts 468/1307 (C), Wit 208/1800 (E), Skill Pts 116.
   - Choices panel, scrolled: header still visible with the trainee card "[Rosy Dreams] Rice Shower" and "Full Stats"; tail of option 1 ("Branch 2: Random 1 attribute(s) +15"); option 2 banner "How about a coin for each Goddess?" with "Branch 1: Mood +1, Random 1 attribute(s) +30" and "Branch 2: Random 1 attribute(s) +15"; option 3 banner "Try tossing in a whole bunch!" with the same two branches.
   - Close button.
5. **Primary action:** Tap one of the three choice banners.
6. **Secondary actions:** Effects; Full Stats; Details.
7. **Layout hierarchy:** As frame 002421; the only change is the scroll offset inside the right panel.
8. **Visual hierarchy:** The three identical green option banners, then the repeated branch cards; the repetition itself is the salient thing.
9. **What carries state:** Same encodings as 002421. This frame adds the fact that all three options expose the same two branches with the same magnitudes.
10. **Absences:** No probabilities; no differentiator between the three options in outcome terms; no scroll position indicator on the panel.
11. **What this screen teaches the app:** Event options can be outcome-identical while differing only in flavour, so a tool must not present the option list as if the choice were a trade-off.

---

## Screenshot 2026-07-18 002941

Frame: `docs/game-screenshots/Screenshot 2026-07-18 002941.png`, 1920×1080. Read 2026-10-03 by the Phase B extraction pass. Erratum, follow-up audit of 2026-10-03: this note previously claimed to also cover `Screenshot 2026-07-18 161122.png`. That fold is withdrawn. 161122 is a different meeting (five Kyoto legs, all "WIN") behind a different background panel (Scheduled Races rather than the Log), so it is not the same screen in the same state, and it now carries its own note.

1. **Category:** career-progression
2. **Screen name:** "RACE FINISHED" (heading banner). Background: the Log panel, dimmed.
3. **Screen purpose:** The player reads a team match result in which every race was won.
4. **Information presented:**
   - Heading: "RACE FINISHED" on a green ribbon.
   - Five match rows: "Race 1" Tokyo Turf 1400m (Sprint) Left; "Race 2" Tokyo Dirt 1600m (Mile) Left; "Race 3" Tokyo Turf 3400m (Long) Left; "Race 4" Tokyo Turf 1800m (Mile) Left; "Race 5" Tokyo Turf 2000m (Medium) Left. Each carries a blue "Wint" badge and a going word with weather icons (Firm or Good).
   - Each row: orange "WIN", three own-team cards with placement badges (1st/3rd/2nd; 3rd/1st/2nd; 1st/4th/6th; 1st/2nd/6th; 1st/2nd/9th), "VS", three opponent cards with placements (4th/5th/empty; 4th/5th/empty; 2nd/3rd/5th; 5th/10th/empty; 4th/3rd/7th).
   - Green "Next" button. Background Log: an outcome card ("Speed went up by 3. Friendship with Shuko Bankshot went up by 2." partially legible) and a green phase bar "Before the Third Round of the Unity Cup".
   - Nav rail behind the dim, Log active.
5. **Primary action:** Tap Next.
6. **Secondary actions:** None visible.
7. **Layout hierarchy:** Same surface as frame 235529: full-width ribbon heading, five stacked match rows, Next centred at the bottom. Refer to that note for the sketch.
8. **Visual hierarchy:** The five orange WIN words first (a column of identical verdicts). The gold 1st badges second. The plates third.
9. **What carries state:** As 235529, plus: the season badge changes with the calendar ("Wint" here versus "Spr" on 235529); an unentered opponent slot is a greyscale silhouette.
10. **Absences:** No match score or points; no rank-change readout despite a clean sweep; no per-race times.
11. **What this screen teaches the app:** A five-race sweep is presented with the same density as a mixed result, so the surface carries no celebration state, and the aggregate consequence has to be read elsewhere.

---

## Screenshot 2026-07-18 133128

Frame: `docs/game-screenshots/Screenshot 2026-07-18 133128.png`, 1920×1080. Read 2026-10-03 by the Phase B extraction pass.

1. **Category:** career-progression
2. **Screen name:** No heading in frame; the footer reads "Goals Achieved". Background: "Scheduled Races" on the Senior Year tab, dimmed.
3. **Screen purpose:** The player reviews the career's goal ladder: which objectives are cleared and which comes next.
4. **Information presented:**
   - Speech bubble: "1 turn(s) until your next goal race! Let's do it!"
   - Goal rows, seven visible: four grey rows carrying a red "CLEAR!" stamp and a grade chip ("G2 Place top 5 in Spring S.", "G1 Place top 5 in Japanese Derby", "G1 Place top 3 in Kikuka Sho", "G2 Place top 3 in Nikkei Sho"); a pink "↓ NEXT" pill; a yellow highlighted row "G1 Place 1st in Tenno Sho (Spring)"; two white rows "G1 Place top 3 in Takarazuka Kinen" and "G1 Place 1st in Arima Kinen".
   - Footer strip: "Goals Achieved 5/8".
   - Green "Next" button; Skip and Quick behind the dim; nav rail with Agenda active.
5. **Primary action:** Tap Next to return to the hub.
6. **Secondary actions:** Scroll the list; Skip or Quick.
7. **Layout hierarchy:** A single centred column: speech bubble at the top, the stacked goal rows, the NEXT divider, the remaining goals, and the Goals Achieved footer near the bottom, with Next below it. The dimmed calendar sits behind on the right.
8. **Visual hierarchy:** The yellow NEXT row first (the only warm-filled card). The red CLEAR! stamps second, as a repeated band. The "5/8" footer third.
9. **What carries state:** Goal status is three treatments: grey with a CLEAR! stamp, yellow highlighted for the next, plain white for later; each row's requirement is a grade chip plus a sentence naming the race and the placement; progress is a single ratio in the footer.
10. **Absences:** No dates or turns attached to each goal; no indication of what failing a goal costs; no per-goal reward; the top row is cut, so the list's full length is only knowable from the 5/8 footer.
11. **What this screen teaches the app:** The career's objective list is a linear ladder with exactly one "next", so a run view can model goals as an ordered sequence with a status enum rather than a set of independent targets.

---

## Screenshot 2026-07-18 160543

Frame: `docs/game-screenshots/Screenshot 2026-07-18 160543.png`, 1920×1080. Read 2026-10-03 by the Phase B extraction pass.

1. **Category:** career-progression
2. **Screen name:** "Team Showdown" (top-left screen chip) with "Select Opponent" as the panel heading; "Scheduled Races" panel on the right half.
3. **Screen purpose:** The player chooses which of three ranked opponent teams to race against in the Team Showdown, with the run's race agenda visible alongside.
4. **Information presented:**
   - Team status bar: a B-rank laurel, team name "Blue Bloom", an "Up-and-Coming" tag, current rank "12th", and an info button.
   - Three opponent cards, each with rank numeral, laurel grade, three opponent portraits, team name, and a reward line: "Rank 9", B laurel, "Daydreamers", "Win to become rank 6." (highlighted card with green corner brackets); "Rank 15", C laurel, "Intelligentsia", "Win to become rank 8."; "Rank 20", D laurel, "Squad Step", "Win to become rank 10."
   - "Select Opponent" confirm button; Back button; "Skip Off" and "Quick" toggles; a menu button.
   - Scheduled Races panel: year tabs "Junior Year", "Classic Year", "Senior Year" (active); a 4 by 12 grid of half-month cells from "Early Jan" to "Late Dec"; most cells are greyed plus-slots; "Late Jun" holds a Goal card "Takarazuka Kinen" with a Goal pennant; "Early Oct" holds "Kyoto Daisinten" marked "Scheduled"; "Late Dec" holds a Goal card "Arima Kinen"; one further card sits behind a "Goal" flag around "Late Apr" row area; Reset and "My Agendas" buttons.
   - Right nav rail: Jukebox, Sparks, Log, Career Profile, Agenda (active, green), Item Request, Menu (NEW).
5. **Primary action:** Select one of the three opponent teams and confirm with Select Opponent.
6. **Secondary actions:** Back; toggle Skip and Quick race presentation; open Reset or My Agendas on the calendar; navigate the rail.
7. **Layout hierarchy:** Left half: the Team Showdown flow, screen chip top-left, team status bar across the top, three stacked opponent cards, confirm button centred below, utility toggles along the bottom edge. Right half: the Scheduled Races card, year tabs over a 4-column month grid, footer controls. Far right: the nav rail. Both halves share one white app background.
8. **Visual hierarchy:** The highlighted top opponent card first (green corner brackets and larger size). The gold rank numerals second. The two Goal calendar cards third (red pennants on pale cards among grey slots). The confirm button is bright green but sits below the fold of the cards' visual weight.
9. **What carries state:** Team rank is a numeral with an ordinal suffix and a laurel grade; the three opponents differ in rank numeral, laurel colour (pink, green, blue) and reward text; the player's prospective rank after a win is stated in each reward line; calendar cells encode state by fill: greyed unavailable, white open, pale card with pennant for goals, blue art card marked "Scheduled" for an entry already made; the active year tab is colour-inverted; Skip Off/Quick are toggle pills.
10. **Absences:** No opponent team stat totals or power ratings on the cards; no predicted win chance; no reward beyond the rank change line; the calendar shows no fan or prize values on its cells.
11. **What this screen teaches the app:** An opponent choice is framed purely as a rank ladder (current rank, three reachable ranks), so the decision the game surfaces is "how far up can I climb" and never "how strong is the opposition numerically".

---

## Screenshot 2026-07-18 161122

Frame: `docs/game-screenshots/Screenshot 2026-07-18 161122.png`, 1920×1080. Read 2026-10-03 by the Phase B follow-up dispatch, which un-folded this frame from `Screenshot 2026-07-18 002941.md`. Canonical cluster: `clusters.json` rep of a 2-member cluster whose other member is `Screenshot 2026-07-18 161325.png`, not `002941`.

1. **Category:** career-progression
2. **Screen name:** "RACE FINISHED" (heading banner). Background: the "Scheduled Races" panel, dimmed.
3. **Screen purpose:** The player reads the per-leg result of a completed team meeting before continuing.
4. **Information presented:**
   - Five stacked leg cards, each an orange "WIN" at the left, then a three-avatar own-team group, "VS", then a three-avatar opponent group. Each card's green header line carries the leg number, the venue string, a pink "Spr" season badge, a weather glyph, the going, and a star icon at the right.
   - Leg 1: "Race 1", "Kyoto Turf 2000m (Medium) Right / Inner", "Good". Own badges read 1st, 4th, 2nd; opponent badges 5th, 6th, 5th.
   - Leg 2: "Race 2", "Kyoto Turf 1200m (Sprint) Right / Inner", "Firm". Own 1st, 2nd, 3rd; opponents 4th, 5th, then an unoccupied slot rendered as a grey silhouette.
   - Leg 3: "Race 3", "Kyoto Turf 3000m (Long) Right / Outer", "Firm". Own 3rd, 1st, 4th; opponents 2nd, 6th, 5th.
   - Leg 4: "Race 4", "Kyoto Dirt 1800m (Mile) Right", "Firm". Own 1st, 3rd, 2nd; opponents 4th, 6th, then an unoccupied slot.
   - Leg 5: "Race 5", "Kyoto Turf 1600m (Mile) Right / Inner", "Firm". Own 1st, 2nd, 5th; opponents 3rd, 6th, 4th.
   - Avatars carry rarity tags ("SSR", "R") at the upper left and a place badge over the lower left of each portrait. The place badges are small glyphs read at full frame size.
   - Green "Next" button. Right half: the "Scheduled Races" grid on the "Senior Year" tab, dimmed, with race cards in the Late Jun, Early Oct and Late Dec cells and a highlighted Late Apr cell. Nav rail with "Agenda" active.
5. **Primary action:** Next.
6. **Secondary actions:** None in frame. The dimmed Scheduled Races grid and the nav rail are not reachable behind the overlay.
7. **Layout hierarchy:** Heading banner, then five equal leg cards top to bottom, then Next. The dimmed calendar and nav rail sit behind the whole stack.
8. **Visual hierarchy:** The five orange "WIN" words first, because they repeat and are the only saturated text. The leg header strips second (green, one per card). The avatars third. Next fourth.
9. **What carries state:** Result per leg is the "WIN" word plus the place badges on each avatar; field completeness is the presence or absence of an opponent avatar (two legs show an unoccupied slot as a grey silhouette); the meeting's identity is carried by the venue strings, which are all Kyoto here.
10. **Absences:** No finishing times, no margins, no favourite odds, no team names, no character names anywhere in frame, no aggregate score for the meeting, and no indication of which round of the team competition this was.
11. **What this screen teaches the app:** A clean sweep renders as five identical "WIN" words with no aggregate, so the count of legs and the per-leg placements are the only record the client gives; a tool that tracks team results has to derive the sweep itself. Two legs also carry an unoccupied opponent slot, so an opponent field is not always full and a stored result set must allow a short group rather than assume three.

---

