# Source-Backed Reconstruction Mapping

## Source

`docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md` — 68 client frames captured 2026-10-03
Implementation: `database/seeders/RosyDreamsRiceShowerSeeder.php` (in both integration workspace and original repo untracked set)

## Reconstruction Scope

**NOT** run against canonical database. Run only on isolated/scratch database for reconstruction preview.

### Career Run (training_rums)

| Field | Value | Source |
|-------|-------|--------|
| umamusume_id | 30 (Rice Shower) | §1.2, seeder UMAMUSUME_ID |
| character_card_id | 31 ([Rosy Dreams]) | §1.2, seeder CHARACTER_CARD_ID |
| scenario | unity_cup | §1.1 |
| status | Active | §1.1 |
| turn | 67 of 72 | §1.1, Senior Early October |
| notes | Unity Cup, [Rosy Dreams] Rice Shower, read off 68 client frames on 2026-10-03. Senior Year Early October, turn 67 of 72, five turns until the finals. Fans 209,245 against a Top Star ladder of 240,000. Energy reads low on the bar and the client is printing its injury warning, but no number is shown, so none is stored. Team Blue Bloom sits at Rank S with 55 Unity Trainings and 11 combined bursts. | §1.1-1.8 |
| career_position | Senior, October, Early, turnIndex 67 | CareerCalendar::positionToTurnIndex |
| team_name | Blue Bloom | §1.8 |
| team_rank | S | §1.8 Team Info panel |
| team_stat_grades | Speed A, Stamina A, Power A, Guts S, Wit A | §1.8 |
| team_league_placement | 8 | §1.8 |
| team_preseason_wins | 4 | §1.8 |
| unity_trainings_count | 55 | §1.8 |
| spirit_bursts_count | 6 | §1.8 |
| extreme_bursts_count | 5 | §1.8 |
| mode | Snapshot | §1.1 |
| cap_speed | 1325 | §1.2 |
| cap_stamina | 1322 | §1.2 |
| cap_power | 1368 | §1.2 |
| cap_guts | 1308 | §1.2 |
| growth_rate | speed 0, stamina 10, power 0, guts 20, wit 0 | §1.2 |
| trainee_rarity | 3 | §1.2 |
| potential_level | 2 | §1.2 |
| import_source | docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md (Run Rosy Dreams Rice Shower) | seeder SOURCE_FILE |

### Turn Entry (turn_entries, turn 67)

| Field | Value |
|-------|-------|
| turn | 67 |
| speed | 607 |
| stamina | 577 |
| power | 549 |
| guts | 736 |
| wit | 397 |
| sp | 173 |
| condition | null (not recorded) |
| energy | null (not printed by client) |
| mood | Great |
| fans | 209245 |

### Roster Members (18 members, §1.9)

| # | Name | Origin | Support Card ID | Grade | Distance | ACE | Trainee |
|---|------|--------|-----------------|-------|----------|-----|---------|
| 1 | Haru Urara | support | 266 | A | Dirt | yes | no |
| 2 | Tokai Teio | support | 250 | B | Medium | yes | no |
| 3 | Nishino Flower | support | 265 | B | Mile | yes | no |
| 4 | Super Creek | support | 263 | B | (none) | no | no |
| 5 | Biko Pegasus | support | 267 | C | (none) | no | no |
| 6 | Matikanetannhauser | support | 277 | C | (none) | no | no |
| 7 | Fine Motion | guest | null | B | (none) | no | no |
| 8 | King Halo | guest | null | B | (none) | no | no |
| 9 | Seeking the Pearl | guest | null | C | Sprint | yes | no |
| 10 | Shinko Windy | guest | null | C | (none) | no | no |
| 11 | Inari One | guest | null | C | (none) | no | no |
| 12 | Nakayama Festa | guest | null | C | (none) | no | no |
| 13 | Agnes Digital | guest | null | C | (none) | no | no |
| 14 | Eishin Flash | guest | null | C | (none) | no | no |
| 15 | Matikanefukukitaru | guest | null | C | (none) | no | no |
| 16 | Taiki Shuttle | guest | null | C | (none) | no | no |
| 17 | Gold Ship | guest | null | C | (none) | no | no |
| 18 | Rice Shower | trainee | null | null | Long | yes | yes |

Each member carries full stat line (speed/stamina/power/guts/wit), stat caps, and aptitudes. See seeder `ROSTER` constant for exact values.

### Deck Slots (6, §1.4)

| Slot | Support Card ID | Card ID | Ownership | Level | Name |
|------|-----------------|---------|-----------|-------|------|
| 1 | 30003 | 250 | OWNED | 35 | Tokai Teio [Dream Big!] |
| 2 | 30018 | 265 | OWNED | 30 | Nishino Flower [Even the Littlest Bud] |
| 3 | 30020 | 267 | OWNED | 30 | Biko Pegasus [Double Carrot Punch!] |
| 4 | 30016 | 263 | RENTED | 50 | Super Creek [Piece of Mind] (Friends slot) |
| 5 | 30030 | 277 | OWNED | 30 | Matikanetannhauser [Just Keep Going] |
| 6 | 30019 | 266 | OWNED | 35 | Haru Urara [Urara's Day Off!] |

### Race Entries (5)

| Slot | Race | Status | Placement |
|------|------|--------|-----------|
| 264 | Nikkei Sho | Completed | null (top 3, place not stated) |
| 280 | Tenno Sho (Spring) | Completed | 1 |
| 310 | Takarazuka Kinen | Completed | null (top 3, place not stated) |
| 355 | Kyoto Daishoten | Entered | null |
| 400 | Arima Kinen | Entered | null |

### Skills (11, §1.3)

| ID | Skill | Acquisition |
|----|-------|-------------|
| 47 | Blue Rose Closer | Acquired |
| 31 | Red Shift/LP1211-M | Acquired |
| 142 | Corner Adept | Acquired |
| 147 | Swinging Maestro | Acquired |
| 174 | Nimble Navigator | Acquired |
| 217 | Steadfast | Acquired |
| 219 | Deep Breaths | Acquired |
| 223 | Extra Tank | Acquired |
| 311 | Pace Chaser Straightaways | Acquired |
| 315 | Shrewd Step | Acquired |
| 148 | Corner Recovery | Acquired (Learn screen, past visible ten) |

### Events (2, turn 67, §1.6)

| Type | Source | Choice | Bond Delta |
|------|--------|--------|------------|
| SupportCard | Super Creek [Piece of Mind] | Ask her to push through the fatigue. | 5 |
| Character | Rice Shower | Have a small snack. | 0 |

### Legacy Selection (§1.5)

| Parent A | Parent B | Affinity |
|----------|----------|----------|
| Vodka [Wild Top Gear] (id=8) | Maruzensky [Formula R] (id=4, guest) | null |

Full spark and ancestor data in seeder `legacySelection()` method.

## Constraints

- **Do not run against canonical database** (`D:\Projects\umamusume-laravel13\database\database.sqlite`)
- **Do not run live seeders** against production
- **Run only on isolated/scratch database** for reconstruction preview
- **Do not fabricate missing historical fields** (energy, condition, current_objective_index, Wit cap column — all intentionally null/absent)
- **Do not overwrite owner edits** — this is the recorded state from 2026-10-03 frames
- Actual gameplay restoration is owner-controlled and separate from application restoration

## Idempotency

The seeder is idempotent by `import_source`: a prior import is deleted with child rows (deck slots, turn events, race entries, skills, turn entries) before re-import. Safe to re-run on scratch database.