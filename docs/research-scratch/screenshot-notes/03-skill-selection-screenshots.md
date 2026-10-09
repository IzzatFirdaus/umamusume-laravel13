# Skill Selection screenshots (merged)

5 frame notes, merged from individual `Screenshot *.md` files.

Each section preserves the original 11-field annotation format.

## Screenshot 2026-07-17 233332

Frame: `docs/game-screenshots/Screenshot 2026-07-17 233332.png`, 1920×1080. Read 2026-10-03 by the Phase B expansion pass. Candidate from the Step 1.1 rule, branch 1: a member of cluster 726 in `docs/design-research/_scratch/clusters.json` (rep `2026-07-18 001811`, count 58) that is a skill-selection screen and is not the cluster rep.

1. **Category:** skill-selection
2. **Screen name:** "Learn" (top-left screen chip). Right panel: "Scheduled Races".
3. **Screen purpose:** The player selects a skill from the catalogue and reads its cost before confirming, with the race calendar open beside it.
4. **Information presented:**
   - Header art over a gym interior, with a "Skill Points 169" strip and a "Full Stats" button.
   - Three skill rows visible. "Firm Conditions ○", green icon, "Moderately increase performance on firm ground.", orange badge "Hint Lvl 1 / 10% OFF!", stepper minus / "81" / plus. "Corner Adept ○", orange runner icon, "Slightly increase velocity on a corner with skilled turning.", badge "Hint Lvl 1 / 10% OFF!", stepper "162". "Swinging Maestro", blue spark icon, "Recover endurance on a corner with efficient turning.", badge "Hint Lvl 1 / 10% OFF!", stepper "323"; this row is rendered on a gold-to-brown gradient with a bracket at its left edge, unlike the two grey rows above it.
   - Confirm (muted green) and Reset.
   - Back; Skip; Quick; menu button.
   - Scheduled Races: "Classic Year" tab active; "Early Apr" highlighted yellow holding "Satsuki Sho" with a pink "Scheduled" pill; "Kyoto Shimbun Hai" (Scheduled, Early May); "Tokyo Yushun" (Goal, Late May); "Kyoto Daishoten" (Scheduled, Early Oct); "Kikuka Sho" (Goal, Late Oct); "Queen Elizabeth Cup" (Scheduled, Early Nov); Reset and "My Agendas".
   - Nav rail: Agenda active.
5. **Primary action:** Set the quantity on a skill row with the steppers, then Confirm.
6. **Secondary actions:** Adjust with minus, Reset, Full Stats, Back, Skip, Quick, calendar controls, rail navigation.
7. **Layout hierarchy:** Left: art header with the Skill Points strip, then three full-width skill cards, then Confirm and Reset centred. Right: the calendar card. Nav rail far right.
8. **Visual hierarchy:** The gold gradient row first, because it is the only card that is not grey. The orange hint badges second. The Skill Points numeral third.
9. **What carries state:** The bracketed gold row marks the row the steppers act on. Price is per row and already discounted by the badge's percentage. Icon colour separates at least three families here: green ground, orange runner, blue spark. The "○" suffix appears on two of the three skill names and not on the third.
10. **Absences:** No skill category labels or filters; no indication of which skills the trainee can still learn versus those gated by aptitude; no running total of the pending selection against the 169 points shown; no confirmation dialog.
11. **What this screen teaches the app:** Selection state and affordability state are separate on this surface: the selected row changes its background, while the point total stays at the header value until Confirm, so a faithful layout must hold a pending basket distinct from the displayed balance.

---

## Screenshot 2026-07-17 233339

Frame: `docs/game-screenshots/Screenshot 2026-07-17 233339.png`, 1920×1080. Read 2026-10-03 by the Phase B expansion pass. Candidate from the Step 1.1 rule, branch 1: a member of cluster 726 in `docs/design-research/_scratch/clusters.json` (rep `2026-07-18 001811`, count 58) that is a skill-selection screen and is not the cluster rep.

1. **Category:** skill-selection
2. **Screen name:** "Learn" (top-left screen chip). Right panel: "Scheduled Races".
3. **Screen purpose:** The player scrolls the skill catalogue and reads which skills are already held against which are still purchasable.
4. **Information presented:**
   - Header art over a gym interior, with a "Skill Points 169" strip and a "Full Stats" button.
   - Three skill rows visible. "Gap Closer", orange runner icon, "Slightly increase spuring ability when positioned toward the back late-race. (Sprint)", orange badge "Hint Lvl 1 / 10% OFF!", stepper minus / "144" / plus. "Productive Plan", orange runner icon, "Slightly widen the margin when positioned toward the front upon approaching mid-race. (Mile)", badge "Hint Lvl 1 / 10% OFF!", stepper "144". "Steadfast", orange runner icon, "Slightly increase velocity and very minimally increase acceleration when pressured on the final corner or later. (Medium)", pink "Obtained" pill, no stepper, the whole row rendered dark grey.
   - Confirm (muted green) and Reset.
   - Back; Skip; Quick; menu button.
   - Scheduled Races: "Classic Year" tab active; "Early Apr" highlighted yellow holding "Satsuki Sho" with a pink "Scheduled" pill; "Kyoto Shimbun Hai" (Scheduled, Early May); "Tokyo Yushun" (Goal, Late May); "Kyoto Daishoten" (Scheduled, Early Oct); "Queen Elizabeth Cup" (Scheduled, Early Nov); Reset and "My Agendas".
   - Obstruction: a Windows Snipping Tool notification ("Screenshot copied to clipboard", "Automatically saved to screenshots folder", "Mark-up and share") over the lower right, covering part of the calendar's Late Aug to Late Oct column and the nav rail below "Career Profile".
   - Nav rail: Agenda active.
5. **Primary action:** Set the quantity on a purchasable row with the steppers, then Confirm.
6. **Secondary actions:** Adjust with minus, Reset, Full Stats, Back, Skip, Quick, calendar controls, rail navigation.
7. **Layout hierarchy:** Left: art header with the Skill Points strip, then three full-width skill cards, then Confirm and Reset centred. Right: the calendar card. Nav rail far right. The obstruction sits above both panels.
8. **Visual hierarchy:** The inverted dark "Steadfast" row first, because it is the only card that is not light. The two orange hint badges second. The Skill Points numeral third.
9. **What carries state:** A held skill is an inverted dark row with a pink "Obtained" pill and no stepper at all, so the stepper's absence is the state marker. Two rows carry the same price of 144 with the same badge, so price is not a function of the skill's position in the list. All three visible rows share the orange runner icon family here.
10. **Absences:** No skill category labels or filters; no running total of the pending selection; no indication of what "Obtained" was paid for or at which point total; the calendar cells behind the obstruction are not readable.
11. **What this screen teaches the app:** The catalogue mixes held and purchasable rows in one scroll with no separator, so a tool that mirrors the list must store an acquired flag per row rather than assume the visible list is a set of offers.

---

## Screenshot 2026-07-17 233344

Frame: `docs/game-screenshots/Screenshot 2026-07-17 233344.png`, 1920×1080. Read 2026-10-03 by the Phase B expansion pass. Candidate from the Step 1.1 rule, branch 1: a member of cluster 726 in `docs/design-research/_scratch/clusters.json` (rep `2026-07-18 001811`, count 58) that is a skill-selection screen and is not the cluster rep.

1. **Category:** skill-selection
2. **Screen name:** "Learn" (top-left screen chip). Right panel: "Scheduled Races".
3. **Screen purpose:** The player scrolls the lower part of the skill catalogue and reads prices where no hint discount applies.
4. **Information presented:**
   - Header art over a gym interior, with a "Skill Points 169" strip and a "Full Stats" button.
   - Three skill rows visible. "Deep Breaths", blue spark icon, "Take a breather and slightly decrease fatigue mid-race. (Long)", no hint badge, stepper minus / "160" / plus. "Subdued Front Runners", red icon, "Slightly increase fatigue for front runners early-race.", orange badge "Hint Lvl 1 / 10% OFF!", stepper "117". "Subdued Pace Chasers", red icon, "Slightly increase fatigue for pace chasers early-race.", no hint badge, stepper "130".
   - Confirm (solid green, brighter than in frames 233332 and 233339) and Reset.
   - Back; Skip; Quick; menu button.
   - Scheduled Races: "Classic Year" tab active; "Early Apr" highlighted yellow holding "Satsuki Sho" with a pink "Scheduled" pill; "Kyoto Shimbun Hai" (Scheduled, Early May); "Tokyo Yushun" (Goal, Late May); "Kyoto Daishoten" (Scheduled, Early Oct); "Queen Elizabeth Cup" (Scheduled, Early Nov); Reset and "My Agendas".
   - Obstruction: a Windows Snipping Tool notification over the lower right, covering part of the calendar's Late Aug to Late Oct column and the nav rail below "Career Profile".
   - Nav rail: Agenda active.
5. **Primary action:** Set the quantity on a row with the steppers, then Confirm.
6. **Secondary actions:** Adjust with minus, Reset, Full Stats, Back, Skip, Quick, calendar controls, rail navigation.
7. **Layout hierarchy:** Left: art header with the Skill Points strip, then three full-width skill cards, then Confirm and Reset centred. Right: the calendar card. Nav rail far right.
8. **Visual hierarchy:** The green Confirm button first in this frame, because it is the only saturated control. The single orange hint badge second. The Skill Points numeral third.
9. **What carries state:** The hint badge is optional per row: two of the three visible rows carry no badge and still show a price, so a badge marks a discounted offer rather than a purchasable one. The Confirm button renders in two strengths across this series, muted in frames 233332 and 233339 and saturated here. Icon colour separates a red family here that does not appear in frames 233332 or 233339.
10. **Absences:** No skill category labels or filters; no explanation of what the badge's absence means for price; no running total of the pending selection; the calendar cells behind the obstruction are not readable; the top of the list is off-frame so the position of these rows in the catalogue is not shown.
11. **What this screen teaches the app:** A price without a discount badge and a price with one sit in the same column, so a tool that displays cost must carry the discount as its own field rather than derive it from the number, and the two Confirm strengths mean enabled state is visible on this surface and has to be modelled.

---

## Screenshot 2026-07-17 234453

Frame: `docs/game-screenshots/Screenshot 2026-07-17 234453.png`, 1920×1080. Read 2026-10-03 by the Phase B extraction pass.

1. **Category:** skill-selection
2. **Screen name:** "Learn" (top-left screen chip). Right panel: "Log".
3. **Screen purpose:** The player spends Skill Points to learn skills, with hint discounts applied per skill.
4. **Information presented:**
   - Header banner: the trainee art with a "Skill Points 308" strip across it.
   - Skill rows, three visible: "Resplendent Red Ace", description "Very slightly swell with the determination to be number one when positioned toward the front in the second half of the race.", an orange badge "Hint Lv 1 / 10% OFF!", a cost stepper showing minus button, "180", plus button. "Cut and Drive!", "Slightly increase velocity when positioned toward the front with 200m or less remaining.", badge "Hint Lv 2 / 20% OFF!", stepper "160". "Red Shift/LP1211-M", "Slightly increase acceleration when positioned toward the front on the final corner or later.", a pink "Obtained" badge, row greyed with no stepper.
   - Confirm (green, disabled styling) and Reset buttons.
   - Back; Skip and Quick; menu button.
   - Log panel: the same Rice Shower, outcome, phase bar and Tazuna Hayakawa cards as frame 234444.
   - Nav rail: Log active.
5. **Primary action:** Add a skill to the purchase with the plus button, then Confirm.
6. **Secondary actions:** Adjust with minus, Reset the selection, Back, Skip or Quick, scroll the Log.
7. **Layout hierarchy:** Left half: art header with the Skill Points strip, then a scrollable list of full-width skill cards, each with icon left, name and description centre, badge and stepper right. Confirm and Reset centred under the list. Right half: Log. Nav rail far right.
8. **Visual hierarchy:** The orange hint badges first (saturation against grey cards). The Skill Points strip second (large numeral over the art). The green Confirm third; its muted green signals no selection yet.
9. **What carries state:** Affordance is the Skill Points numeral; discount is the badge's percent text tied to hint level; learnability is stepper presence versus the greyed row with an "Obtained" pill; pending purchase is the stepper's quantity; the red-to-grey cost text presumably tracks affordability, though this frame shows no insufficient state.
10. **Absences:** No skill categorisation or type grouping visible; no indication of what a hint level means beyond the discount badge; no predicted effect of the skill on races; the list shows no filter or search.
11. **What this screen teaches the app:** A purchase surface prices each item individually from an external modifier (hint level), so cost is per-row derived data, not a global rate.

---

## Screenshot 2026-07-17 234521

Frame: `docs/game-screenshots/Screenshot 2026-07-17 234521.png`, 1920×1080. Read 2026-10-03 by the Phase B extraction pass.

**Obstruction:** a Snipping Tool notification ("Screenshot copied to clipboard") covers the lower right Log panel and nav rail.

1. **Category:** skill-selection
2. **Screen name:** "Learn" (top-left screen chip). Right panel: "Log".
3. **Screen purpose:** The player selects a skill to purchase; one row is in the selected state.
4. **Information presented:**
   - Skill Points strip: "Skill Points 308" over the trainee art.
   - Skill rows, three visible: "Plan X", "Increase passing ability when positioned toward the front upon approaching late-race. (Sprint)", badge "Hint Lv 1 / 10% OFF!", stepper minus / "272" / plus; this row renders in a gold-to-orange gradient, unlike the grey rows below. "Countermeasure", "Slightly increase passing ability when positioned toward the front upon approaching late-race. (Sprint)", "Hint Lv 2 / 20% OFF!", stepper "128". "Soft Step", "Slightly decrease fatigue when moving sideways. (Medium)", blue icon (the other two use an orange runner icon), "Hint Lv 3 / 30% OFF!", stepper "112".
   - Confirm (muted green) and Reset.
   - Log panel as in frame 234444, its first two dialogue cards headed "Rice Shower", with the Snipping Tool obstruction over its lower right.
5. **Primary action:** Confirm the selected skill purchase.
6. **Secondary actions:** Move the selection with the plus and minus steppers, Reset, Back, Skip or Quick.
7. **Layout hierarchy:** Same shell as frame 234453: art header with Skill Points strip, full-width skill card list, Confirm and Reset centred, Log right, nav rail far right. Refer to that note for the sketch.
8. **Visual hierarchy:** The gold selected row first (the only saturated card in the list). The orange hint badges second. The Skill Points numeral third.
9. **What carries state:** Selection is the gold gradient row treatment; hint level maps to discount percent (Lv 1 to 10%, Lv 2 to 20%, Lv 3 to 30% in this frame); the stepper shows the row's current cost; icon colour varies by skill (orange runner versus blue spark) and appears to encode a skill category; Confirm stays muted until a purchase is pending.
10. **Absences:** No explanation of the "(Sprint)" and "(Medium)" suffixes beyond the name; no distinction visible between the selected row's stepper and the others'; no preview of what the skill does in race terms beyond the description sentence.
11. **What this screen teaches the app:** Selection on a purchase list is a whole-row colour state, and each row's price already includes its discount, so the stepper never shows two numbers (base and discounted) at once.

---

