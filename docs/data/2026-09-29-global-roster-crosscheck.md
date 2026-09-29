# Global roster cross-check — every `[Global]` costume card against two Tier A pages

**Dated observation, read 2026-09-29.** This file records what the two Tier A witnesses said on
that day, not what they say now: banner cycles move, and a card listed `N/A` today carries a date
the next time the page is edited. Read it as a snapshot with the same standing as the engine's
dated snapshot policy (`ADR-0003` Amendment R3), and re-run before relying on any row.

It exists because of one line in `docs/SOURCE-OF-TRUTH.md` §5: *"A Tier B dataset (GameTora) needs
A- or S-tier confirmation before a claim becomes app data."* Every card in
`character_cards` comes from a Tier B export, so the title, the Global date and the rarity a
Trainer reads on screen are Tier B claims until a Tier A page attests them. The owner ruled the
confirmation **deep** — all of them, not a spot-check — and this file is that crossing.

## Sources

| Role | Tier | Page | Read |
|---|---|---|---|
| Tier B dataset | [B] | `https://gametora.com/data/umamusume/character-cards.e9e9ee6d.json` | 2026-09-29 UTC |
| Tier A witness | [A] | `https://umamusu.wiki/Game:List_of_Trainees` | 2026-09-29 UTC |
| Tier A witness | [A] | `https://game8.co/games/Umamusume-Pretty-Derby/archives/535926` | 2026-09-29 UTC |

The GameTora URL was re-resolved the same day through the publisher's manifest
(`https://gametora.com/data/manifests/umamusume.json`), which reported
`"character-cards": "e9e9ee6d"` — the same hash `config/uma.php` pins, so the pinned document and
the live document were one revision at read time. `KI-24` is the reason the manifest is checked
rather than the pin trusted: a stale hash answers `200` with the superseded body.

"2026-09-29 UTC" is the UTC date, which is what this file's dated name follows (the repo's
store-UTC rule); the host's own wall clock had already passed midnight into 2026-09-30 at the
moment the three bodies were saved, so a reader comparing this date to local file times is not
looking at a contradiction.

All three bodies were saved to the gitignored `research-scratch/data/` and read from there;
`tools/roster-crosscheck.php` fetches nothing, and edits no title.

The colon in the MediaWiki page name (`Game:List_of_Trainees`) is why these URLs are quoted rather
than linked: naive link handling drops everything before it.

## What was measured, before any comparison

```
rows=268 global=107 trainees=68
```

over `research-scratch/data/json/character-cards.json`, 251,294 bytes, by

```bash
php -r '$r=json_decode(file_get_contents("research-scratch/data/json/character-cards.json"),true); printf("rows=%d global=%d trainees=%d\n",count($r),count(array_filter($r,fn($c)=>is_string($c["release_en"]??null))),count(array_unique(array_column(array_filter($r,fn($c)=>is_string($c["release_en"]??null)),"char_id"))));'
```

`global=107` is the population this file cross-checks: **107 cards over 68 trainees**, out of 268
rows in the export. The remaining 161 rows carry no `release_en`, are `[JP-Only]`, and are out of
scope — this task does not cross-check a card into Global existence.

The shipped parser reads the same 107 out of the same body
(`GametoraCharacterCardParser::parse` returns 107 records, no `card_id` duplicated), so the table
below is the whole `character_cards` population at this hash — not a sample of it.

Two counts moved since the plan was written, and the measurement above is what this file quotes:
the plan said **105** Global cards in `ADR-0008` and the request, and was corrected to **107** when
the hash rotated `679f7c2e` → `e9e9ee6d`. 107 is what the live body says today. No further
rotation was found at read time.

Source-side population, counted from the two Tier A rows this file extracted:

- `umamusu.wiki`: 268 card rows, of which **104** carry a real Global date and **164** read `N/A`
  (104 + 164 = 268). All 104 dated rows match one of the 107 Global cards; **0** match nothing.
- Game8: **105** distinct `Trainee (Costume)` pairs on the page (from 108 tiles, 3 duplicated across
  tables), and all 105 match one of the 107 Global cards; **0** match nothing. Game8's page states no
  Global date and no rarity for a costume, so it witnesses a title and nothing else.

The wiki's table covers the same 268-card universe the export does; Game8's page tiles 105 of the
107. So a card missing from one of these pages is a page not covering it, not a page refuting it —
which is why a missing witness flags rather than deletes, and why the two `single-source` rows below
are recorded as thin evidence rather than as a denial.

## Method

**Comparison.** Titles are compared on folded copies; the strings in the table are the sources' own,
verbatim, brackets included. Three folds, cheapest first, all recorded per row:

1. **tier 1** — surrounding `[ ]` stripped, case folded, whitespace runs collapsed. This is the
   rule the task brief sets, and it is why `umamusu.wiki` printing `Special Dreamer` against
   GameTora's `[Special Dreamer]` is one claim rather than a conflict.
2. **tier 2** — the repository's own `NameNormalizer::normalize()` applied to the same
   bracket-stripped title: NFKD, combining marks dropped, then the five characters
   `NameNormalizer::FOLDED_CHARACTERS` already drops for search (`・`, `･`, `-`, the ASCII space and
   the ideographic space). Reused rather than re-invented so this file and the catalog cannot
   disagree about what counts as the same name.
3. **tier 3** — tier 2 with the ornamental glyphs `☆ ★ ♡ ♥ ♪ ✩ ✧ ＋` also removed, because a
   guide page writes `El Numero 1` where the client string is `[El☆Número 1]`.

Tier 2 and tier 3 are a stated **deviation** from the brief's single fold, and nothing rests on them
silently: every row matched above tier 1 is listed in the spelling-variant log below with the tier
that matched, and the verdict counts under the brief's literal tier-1-only rule are given beside the
counts this file delivers. Cost if the deviation is wrong: 9 cards read `two-source-confirmed` that
a strict reading would call `single-source` and therefore flag; the strict flag list is named, so an
owner who prefers it can adopt it without re-running anything.

**Verdict, keyed on `card_id` and on nothing else.** `unconfirmed` answers *"do two independent
sources attest that THIS card exists, with this title, date and rarity?"* — the owner's ruling of
2026-09-29. It is a property of the card, never of the trainee a source happens to attach it to, so
this table records no trainee association and no verdict is keyed on `(card_id, umamusume_id)`: a
card that re-parents keeps its verdict, because the verdict was never about the association.

- `two-source-confirmed` — both Tier A pages carry a row whose title matches the card's, and neither
  page disagrees with the export on date or rarity.
- `single-source` — exactly one Tier A page witnesses the card. The Tier B export stands with one
  witness, which is not the confirmation §5:152 asks for.
- `conflict` — the export and a Tier A page disagree on the Global date or the rarity, however many
  pages carry the card.
- `unwitnessed` — neither page carries the card at all. Kept as its own class so "nobody has looked
  at this one" never reads as confirmed.

**Disagreement rules, in order, numbered as the brief numbers them.**

1. **Title.** GameTora's `title_en_gl` is the `[Global]` client string, so it wins and the other
   spelling is recorded as differing — never edited into agreement. `CONSTRAINTS.md` C-4's
   verbatim-names bullet is what forbids the edit; the display path is where a lore guard belongs.
2. **Global release date.** `conflict`, and `unconfirmed = true`. A date is not a name: the client
   string does not arbitrate it.
3. **English trainee name.** Both spellings recorded, the client string kept, and the other form
   belongs in `umamusume_aliases` (FR-A-2) so it stays findable. Recorded below; it does not change
   a card's verdict, because a verdict is about the card.
4. **Rarity.** `conflict` likewise — rarity feeds the H1 max-rarity claim, so an uncrossed value is
   visible wrong data.
5. A conflict is never resolved by preference, and never by picking whichever source agrees with the
   brief.

**One judgement this file had to make, stated rather than buried.** `umamusu.wiki` prints `N/A` in
its Global-date column for 164 of its 268 rows. Where the export carries a real Global date and the
wiki row for that same title prints `N/A`, this file calls it a **date disagreement → `conflict`**,
not an absence of claim: the two pages make opposite statements about whether the card reached
`[Global]`, and `[Global]` is the only precondition this table has. Cost if that is wrong: 3 cards
(`100103`, `100802`, `100902`) carry a flag they do not need. The reverse reading — `N/A` means the
wiki never witnessed the date, so `two-source-confirmed` — would assert a confirmation the page does
not give, so `conflict` is the reading this file keeps.

## The table

Generated by `php tools/roster-crosscheck.php > docs/data/roster-crosscheck-table.md`; the same rows
are embedded here so this file reads on its own. `not listed` means that page carries no row for the
card at all; `N/A` is the wiki's own cell.

| card_id | GameTora title | Game8 title | umamusu.wiki title | GameTora date | Tier A date | rarity | verdict |
|---|---|---|---|---|---|---|---|
| 100101 | [Special Dreamer] | Special Dreamer | Special Dreamer | 2025-06-26 | 2025-06-26 | 3 | two-source-confirmed |
| 100102 | [Hopp'n♪Happy Heart] | Hopp'n♪Happy Heart | Hopp'n♪Happy Heart | 2025-10-14 | 2025-10-14 | 3 | two-source-confirmed |
| 100201 | [Innocent Silence] | Innocent Silence | Innocent Silence | 2025-06-26 | 2025-06-26 | 3 | two-source-confirmed |
| 100301 | [Peak Joy] | Peak Joy | Peak Joy | 2025-06-26 | 2025-06-26 | 3 | two-source-confirmed |
| 100302 | [Beyond the Horizon] | Beyond the Horizon | Beyond the Horizon | 2025-07-16 | 2025-07-16 | 3 | two-source-confirmed |
| 100401 | [Formula R] | Formula R | Formula R | 2025-06-26 | 2025-06-26 | 3 | two-source-confirmed |
| 100402 | [Hot☆Summer Night] | Hot☆Summer Night | Hot☆Summer Night | 2025-10-14 | 2025-10-14 | 3 | two-source-confirmed |
| 100501 | [Shooting Star Revue] | Shooting Star Revue | Shooting Star Revue | 2025-10-02 | 2025-10-02 | 3 | two-source-confirmed |
| 100601 | [Starlight Beat] | Starlight Beat | Starlight Beat | 2025-06-26 | 2025-06-26 | 3 | two-source-confirmed |
| 100701 | [Red Strife] | Red Strife | Red Strife | 2025-06-26 | 2025-06-26 | 2 | two-source-confirmed |
| 100801 | [Wild Top Gear] | Wild Top Gear | Wild Top Gear | 2025-06-26 | 2025-06-26 | 2 | two-source-confirmed |
| 100901 | [Peak Blue] | Peak Blue | Peak Blue | 2025-06-26 | 2025-06-26 | 2 | two-source-confirmed |
| 101001 | [Wild Frontier] | Wild Frontier | Wild Frontier | 2025-06-26 | 2025-06-26 | 3 | two-source-confirmed |
| 101101 | [Stone-Piercing Blue] | Stone-Piercing Blue | Stone-Piercing Blue | 2025-06-26 | 2025-06-26 | 2 | two-source-confirmed |
| 101102 | [Saintly Jade Cleric] | Saintly Jade Cleric | Saintly Jade Cleric | 2025-09-21 | 2025-09-21 | 3 | two-source-confirmed |
| 101201 | [Azure Amazon] | Azure Amazon | Azure Amazon | 2025-09-17 | 2025-09-17 | 3 | two-source-confirmed |
| 101301 | [Frontline Elegance] | Frontline Elegance | Frontline Elegance | 2025-06-26 | 2025-06-26 | 3 | two-source-confirmed |
| 101302 | [End of the Skies] | End of the Skies | End of the Skies | 2025-07-16 | 2025-07-16 | 3 | two-source-confirmed |
| 101401 | [El☆Número 1] | El Numero 1 | El☆Número 1 | 2025-06-26 | 2025-06-26 | 2 | two-source-confirmed |
| 101402 | [Kukulkan Warrior] | Kukulkan Warrior | Kukulkan Warrior | 2025-09-21 | 2025-09-21 | 3 | two-source-confirmed |
| 101501 | [O Sole Suo!] | O Sole Suo! | O Sole Suo! | 2025-06-26 | 2025-06-26 | 3 | two-source-confirmed |
| 101601 | [Maverick] | Maverick | Maverick | 2025-08-20 | 2025-08-20 | 3 | two-source-confirmed |
| 101701 | [Emperor's Path] | Emperor's Path | Emperor's Path | 2025-06-26 | 2025-06-26 | 3 | two-source-confirmed |
| 101801 | [Empress Road] | Empress Road | Empress Road | 2025-06-26 | 2025-06-26 | 2 | two-source-confirmed |
| 101802 | [Quercus Civilis] | Quercus Civilis | Quercus Civilis | 2025-08-28 | 2025-08-28 | 3 | two-source-confirmed |
| 101901 | [Full-Color Fangirling] | Full-Color Fangirling | Full-Color Fangirling | 2025-11-19 | 2025-11-19 | 3 | two-source-confirmed |
| 102001 | [Reeling in the Big One] | Reeling in the Big One | Reeling in the Big One | 2025-09-07 | 2025-09-07 | 3 | two-source-confirmed |
| 102301 | [pf. Winning Equation...] | pf. Winning Equation... | pf. Winning Equation... | 2025-07-10 | 2025-07-10 | 3 | two-source-confirmed |
| 102401 | [Scramble☆Zone] | Scramble Zone | Scramble☆Zone | 2025-06-26 | 2025-06-26 | 2 | two-source-confirmed |
| 102402 | [Sunlight Bouquet] | Sunlight Bouquet | Sunlight Bouquet | 2025-08-28 | 2025-08-28 | 3 | two-source-confirmed |
| 102601 | [MB-19890425] | MB-19890425 | MB-19890425 | 2025-07-02 | 2025-07-02 | 3 | two-source-confirmed |
| 102701 | [Down the Line] | Down the Line | Down the Line | 2025-06-26 | 2025-06-26 | 1 | two-source-confirmed |
| 102801 | [Buono ☆ Alla Moda] | Buono☆Alla Moda | Buono ☆ Alla Moda | 2025-11-11 | 2025-11-11 | 3 | two-source-confirmed |
| 103001 | [Rosy Dreams] | Rosy Dreams | Rosy Dreams | 2025-06-26 | 2025-06-26 | 3 | two-source-confirmed |
| 103201 | [tach-nology] | Tach-nology | tach-nology | 2025-06-26 | 2025-06-26 | 1 | two-source-confirmed |
| 103501 | [Get to Winning!] | Get to Winning! | Get to Winning! | 2025-06-26 | 2025-06-26 | 1 | two-source-confirmed |
| 103701 | [Meisterschaft] | Meisterschaft | Meisterschaft | 2025-10-30 | 2025-10-30 | 3 | two-source-confirmed |
| 103801 | [Fille Éclair] | Fille Éclair | Fille Éclair | 2025-07-27 | 2025-07-27 | 3 | two-source-confirmed |
| 104001 | [Authentic / 1928] | Authentic / 1928 | Authentic / 1928 | 2025-10-07 | 2025-10-07 | 3 | two-source-confirmed |
| 104101 | [Blossom in Learning] | Blossom in Learning | Blossom in Learning | 2025-06-26 | 2025-06-26 | 1 | two-source-confirmed |
| 104501 | [Murmuring Stream] | Murmuring Stream | Murmuring Stream | 2025-06-26 | 2025-06-26 | 2 | two-source-confirmed |
| 104601 | [LOVE☆4EVER] | LOVE☆4EVER | LOVE☆4EVER | 2025-08-11 | 2025-08-11 | 3 | two-source-confirmed |
| 105001 | [Nevertheless] | Nevertheless | Nevertheless | 2025-08-03 | 2025-08-03 | 3 | two-source-confirmed |
| 105201 | [Bestest Prize ♪] | Bestest Prize | Bestest Prize ♪ | 2025-06-26 | 2025-06-26 | 1 | two-source-confirmed |
| 105601 | [Rising☆Fortune] | Rising Fortune | Rising☆Fortune | 2025-06-26 | 2025-06-26 | 1 | two-source-confirmed |
| 105602 | [Lucky Tidings] | Lucky Tidings | Lucky Tidings | 2025-11-06 | 2025-11-06 | 3 | two-source-confirmed |
| 105801 | [Turbulent Blue] | Turbulent Blue | Turbulent Blue | 2025-10-21 | 2025-10-21 | 3 | two-source-confirmed |
| 106001 | [Poinsettia Ribbon] | Poinsettia Ribbon | Poinsettia Ribbon | 2025-06-26 | 2025-06-26 | 1 | two-source-confirmed |
| 106101 | [King of Emeralds] | King of Emeralds | King of Emeralds | 2025-06-26 | 2025-06-26 | 1 | two-source-confirmed |
| 103002 | [Vampire Makeover!] | Vampire Makeover! | Vampire Makeover! | 2025-11-24 | 2025-11-24 | 3 | two-source-confirmed |
| 104502 | [Chiffon-Wrapped Mummy] | Chiffon-Wrapped Mummy | Chiffon-Wrapped Mummy | 2025-11-24 | 2025-11-24 | 3 | two-source-confirmed |
| 103901 | [Princess of Pink] | Princess of Pink | Princess of Pink | 2025-12-01 | 2025-12-01 | 3 | two-source-confirmed |
| 102501 | [Creeping Shadow] | Creeping Shadow | Creeping Shadow | 2025-12-08 | 2025-12-08 | 3 | two-source-confirmed |
| 101702 | [Archer by Moonlight] | Archer by Moonlight | Archer by Moonlight | 2025-12-14 | 2025-12-14 | 3 | two-source-confirmed |
| 104002 | [Autumn Cosmos] | Autumn Cosmos | Autumn Cosmos | 2025-12-14 | 2025-12-14 | 3 | two-source-confirmed |
| 104801 | [Jokester ☆ Vibes] | Jokester ☆ Vibes | Jokester ☆ Vibes | 2025-12-18 | 2025-12-18 | 3 | two-source-confirmed |
| 105901 | [Off the Line] | Off the Line | Off the Line | 2025-12-28 | 2025-12-28 | 3 | two-source-confirmed |
| 100602 | [Ashen Miracle] | Ashen Miracle | Ashen Miracle | 2026-01-05 | 2026-01-05 | 3 | two-source-confirmed |
| 102302 | [Rouge Caroler] | Rouge Caroler | Rouge Caroler | 2026-01-05 | 2026-01-05 | 3 | two-source-confirmed |
| 102201 | [Noble Seamair] | Noble Seamair | Noble Seamair | 2026-01-15 | 2026-01-15 | 3 | two-source-confirmed |
| 102101 | [Fast as Lightning] | Fast as Lightning | Fast as Lightning | 2026-01-22 | 2026-01-22 | 3 | two-source-confirmed |
| 101502 | [New Year, Same Radiance!] | New Year, Same Radiance! | New Year, Same Radiance! | 2026-01-29 | 2026-01-29 | 3 | two-source-confirmed |
| 105202 | [New Year ♪ New Urara!] | New Year ♪ New Urara! | New Year ♪ New Urara! | 2026-01-29 | 2026-01-29 | 3 | two-source-confirmed |
| 106901 | [Strength in Full Bloom] | Strength in Full Bloom | Strength in Full Bloom | 2026-02-11 | 2026-02-11 | 3 | two-source-confirmed |
| 102602 | [CODE: ICING] | CODE: ICING | CODE: ICING | 2026-02-18 | 2026-02-18 | 3 | two-source-confirmed |
| 103702 | [Precise Chocolatier] | Precise Chocolatier | Precise Chocolatier | 2026-02-18 | 2026-02-18 | 3 | two-source-confirmed |
| 107101 | [Crystalline] | Crystalline | Crystalline | 2026-02-25 | 2026-02-25 | 3 | two-source-confirmed |
| 103301 | [Starry Nocturne] | Starry Nocturne | Starry Nocturne | 2026-03-05 | 2026-03-05 | 3 | two-source-confirmed |
| 106201 | [Clippety-Tippety-Clop] | Clippety Tippety Clop | Clippety-Tippety-Clop | 2026-03-12 | 2026-03-12 | 2 | two-source-confirmed |
| 106801 | [Gilded Shrine to Glory] | Gilded Shrine to Glory | Gilded Shrine to Glory | 2026-03-12 | 2026-03-12 | 3 | two-source-confirmed |
| 106701 | [Natural Brilliance] | Natural Brilliance | Natural Brilliance | 2026-03-22 | 2026-03-22 | 3 | two-source-confirmed |
| 107401 | [Brunissage Line] | Brunissage Line | Brunissage Line | 2026-03-26 | 2026-03-26 | 3 | two-source-confirmed |
| 100502 | [Succès Étoilé] | Succès Étoilé | Succès Étoilé | 2026-04-05 | 2026-04-05 | 3 | two-source-confirmed |
| 102002 | [Soirée des Chatons] | Soirée des Chatons | Soirée des Chatons | 2026-04-05 | 2026-04-05 | 3 | two-source-confirmed |
| 105101 | [Layered Petals] | Layered Petals | Layered Petals | 2026-04-12 | 2026-04-12 | 3 | two-source-confirmed |
| 107201 | [Blazed Head, Covered Fists] | Blazed Head, Covered Fists | Blazed Head, Covered Fists | 2026-04-20 | 2026-04-20 | 3 | two-source-confirmed |
| 106002 | [Run & Win] | Run & Win | Run & Win | 2026-04-26 | 2026-04-26 | 3 | two-source-confirmed |
| 106102 | [Cheerleader in Noble White] | Cheerleader in Noble White | Cheerleader in Noble White | 2026-04-26 | 2026-04-26 | 3 | two-source-confirmed |
| 103101 | [Always Electrifying] | Always Electrifying | Always Electrifying | 2026-04-30 | 2026-04-30 | 3 | two-source-confirmed |
| 106401 | [Line Breakthrough] | Line Breakthrough | Line Breakthrough | 2026-05-10 | 2026-05-10 | 3 | two-source-confirmed |
| 102202 | [Titania] | Titania | Titania | 2026-05-18 | 2026-05-18 | 3 | two-source-confirmed |
| 103802 | [Ma Chérie of the New Moon] | Ma Chérie of the New Moon | Ma Chérie of the New Moon | 2026-05-18 | 2026-05-18 | 3 | two-source-confirmed |
| 103401 | [Edomurasaki] | Edomurasaki | Edomurasaki | 2026-05-28 | 2026-05-28 | 3 | two-source-confirmed |
| 104401 | [Platanus Witch] | Platanus Witch | Platanus Witch | 2026-06-04 | 2026-06-04 | 3 | two-source-confirmed |
| 101002 | [Bubblegum☆Memories] | Bubblegum ☆Memories | Bubblegum☆Memories | 2026-06-11 | 2026-06-11 | 3 | two-source-confirmed |
| 105902 | [Sapphire Sojourn] | Sapphire Sojourn | Sapphire Sojourn | 2026-06-11 | 2026-06-11 | 3 | two-source-confirmed |
| 103601 | [unsigned] | unsigned | unsigned | 2026-06-18 | 2026-06-18 | 3 | two-source-confirmed |
| 100103 | [Ruler of Japan] | Ruler of Japan | Ruler of Japan | 2026-06-25 | N/A | 3 | conflict |
| 100702 | [RUN! RUIN! LAUNCHER!] | not listed | RUN! RUIN! LAUNCHER! | 2026-07-02 | 2026-07-02 | 3 | single-source |
| 101303 | [Fair Lady of the Waves] | not listed | Fair Lady of the Waves | 2026-07-02 | 2026-07-02 | 3 | single-source |
| 105301 | [Iron Ambition] | Iron Ambition | Iron Ambition | 2026-07-07 | 2026-07-07 | 3 | two-source-confirmed |
| 109801 | [Eightfold☆Fortune] | Eightfold ☆Fortune | Eightfold☆Fortune | 2026-07-16 | 2026-07-16 | 3 | two-source-confirmed |
| 104602 | [Twilight Triumph] | Twilight Triumph | Twilight Triumph | 2026-07-22 | 2026-07-22 | 3 | two-source-confirmed |
| 103502 | [Dream Deliverer] | Dream Deliverer | Dream Deliverer | 2026-07-27 | 2026-07-27 | 3 | two-source-confirmed |
| 105002 | [Difference Engineer] | Difference Engineer | Difference Engineer | 2026-07-27 | 2026-07-27 | 3 | two-source-confirmed |
| 102901 | [Darl'n Snowflake] | Darl'n Snowflake | Darl'n Snowflake | 2026-08-05 | 2026-08-05 | 3 | two-source-confirmed |
| 104201 | [Rocket☆Star] | Rocket☆Star | Rocket☆Star | 2026-08-12 | 2026-08-12 | 3 | two-source-confirmed |
| 101902 | [Fanatic♡Jiangshi] | Fanatic♡ Jiangshi | Fanatic♡Jiangshi | 2026-08-18 | 2026-08-18 | 3 | two-source-confirmed |
| 105802 | [Dot-o'-Lantern] | Dot-o'-Lantern | Dot-o'-Lantern | 2026-08-18 | 2026-08-18 | 3 | two-source-confirmed |
| 108701 | [Flare] | Flare | Flare | 2026-08-25 | 2026-08-25 | 3 | two-source-confirmed |
| 107801 | [Fluttertail Spirit] | Fluttertail Spirit | Fluttertail Spirit | 2026-09-01 | 2026-09-01 | 3 | two-source-confirmed |
| 102102 | [Raging Thunder] | Raging Thunder | Raging Thunder | 2026-09-07 | 2026-09-07 | 3 | two-source-confirmed |
| 103402 | [Golden Dream] | Golden Dream | Golden Dream | 2026-09-07 | 2026-09-07 | 3 | two-source-confirmed |
| 104901 | [Desperate Measures] | Desperate Measures | Desperate Measures | 2026-09-15 | 2026-09-15 | 3 | two-source-confirmed |
| 110001 | [Butterfly Sting] | Butterfly Sting | Butterfly Sting | 2026-09-24 | 2026-09-23 | 3 | conflict |
| 100802 | [Fiery Aqua Vitae] | Fiery Aqua Vitae | Fiery Aqua Vitae | 2026-09-28 | N/A | 3 | conflict |
| 100902 | [Nuit Étoilée de Scarlet] | Nuit Étoilée de Scarlet | Nuit Étoilée de Scarlet | 2026-09-28 | N/A | 3 | conflict |

## Counts, with the arithmetic

Grep-verifiable against `docs/data/roster-crosscheck-table.md` (107 data rows, 1 header row, so
`grep -c '^| '` returns 108):

| verdict | rows |
|---|---|
| `two-source-confirmed` | 101 |
| `single-source` | 2 |
| `conflict` | 4 |
| `unwitnessed` | 0 |

**101 + 2 + 4 + 0 = 107** — the whole Global population, nothing dropped, nothing sampled.

Flag list this file hands to the apply step (`single-source` ∪ `conflict`, 6 cards):

```
100103, 100702, 100802, 100902, 101303, 110001
```

Under the brief's literal tier-1-only fold the same body reads
**92 + 11 + 4 + 0 = 107**, and the flag list grows to those 6 plus the 9 spelling-variant cards
below (`101002, 101401, 101902, 102401, 102801, 105201, 105601, 106201, 109801`). That is the cost
of the deviation in one line: 9 flags either way, and both lists are named here.

## Conflict log

Every row that is not `two-source-confirmed`, plus every title that needed a deeper fold. Nothing
here is reconciled; the client string is what the app stores and the other spelling stays on the
page it came from.

### Global date conflicts (4) — `conflict`, `unconfirmed = true`

| card_id | GameTora title | GameTora Global date | umamusu.wiki Global date | wiki row | Game8 |
|---|---|---|---|---|---|
| 100103 | `[Ruler of Japan]` | 2026-06-25 | `N/A` | wikitable body row 3 (of 268) | witnessed, table 2 row 6 cell 3 |
| 100802 | `[Fiery Aqua Vitae]` | 2026-09-28 | `N/A` | wikitable body row 22 (of 268) | witnessed, table 1 row 2 cell 1 |
| 100902 | `[Nuit Étoilée de Scarlet]` | 2026-09-28 | `N/A` | wikitable body row 24 (of 268) | witnessed, table 1 row 3 cell 1 |
| 110001 | `[Butterfly Sting]` | 2026-09-24 | `2026-09-23` | wikitable body row 218 (of 268) | witnessed, table 1 row 4 cell 1 |

Three of these are the newest and second-newest Global dates in the export (`2026-09-28` is the
maximum over all 107 rows, `2026-09-24` the next), and the wiki has not caught up; `100103` is the
Special Week costume whose wiki row prints `N/A` in **both** date columns while the export gives it a
JP `release` of `2022-07-20` and a Global `release_en` of `2026-06-25`. `110001` disagrees by one day,
which is the shape a release that rolls past midnight in one zone makes — recorded as a disagreement,
not as a timezone question answered here, because the flag exists exactly for the case nobody has
settled. None of the four is reconciled: the date a Trainer sees on screen comes from Tier B alone.

### Rarity conflicts (0)

Measured across the 107 wiki-witnessed rows: **0** disagreements. Every card's `rarity` in the export
equals the star count on the wiki's row for it (`data-sort-value`, falling back to the `★` run). The
H1 max-rarity claim is therefore crossed end to end at this hash.

### `single-source`: one Tier A page witnesses the card (2)

| card_id | GameTora title | witnessing page | silent page |
|---|---|---|---|
| 100702 | `[RUN! RUIN! LAUNCHER!]` | umamusu.wiki, wikitable body row 19 (of 268) | Game8 — the page carries no tile for it at all (`RUIN` appears nowhere in the saved body) |
| 101303 | `[Fair Lady of the Waves]` | umamusu.wiki, wikitable body row 35 (of 268) | Game8 — `Fair Lady` appears nowhere in the saved body |

Both are witnessed by one Tier A page with a date and a rarity that agree with the export, and both
are absent from the other page, which lists 105 of the 107. Absence from a page that is not a
complete costume index is weak evidence, but §5:152 asks for confirmation, not for a rebuttal, so
these two are flagged rather than trusted.

### Title spelling variants (9) — logged, not reconciled, verdict unchanged

Every one of these is a Game8 rendering; the wiki matched at tier 1 on all 107 cards
(96 of 107 are witnessed by **both** pages at tier 1 with no deeper fold; 9 needed one; the remaining
2 have no Game8 row at all — 96 + 9 + 2 = 107).

| card_id | GameTora (client string) | Game8 as printed | tier | Game8 row |
|---|---|---|---|---|
| 101401 | `[El☆Número 1]` | `El Numero 1` | 3 | table 3 row 2 cell 2 |
| 102401 | `[Scramble☆Zone]` | `Scramble Zone` | 3 | table 3 row 1 cell 3 |
| 102801 | `[Buono ☆ Alla Moda]` | `Buono☆Alla Moda` | 2 | table 2 row 19 cell 3 |
| 105201 | `[Bestest Prize ♪]` | `Bestest Prize` | 3 | table 4 row 2 cell 1 |
| 105601 | `[Rising☆Fortune]` | `Rising Fortune` | 3 | table 4 row 1 cell 3 |
| 106201 | `[Clippety-Tippety-Clop]` | `Clippety Tippety Clop` | 2 | table 3 row 1 cell 1 |
| 101002 | `[Bubblegum☆Memories]` | `Bubblegum ☆Memories` | 2 | table 2 row 7 cell 3 |
| 109801 | `[Eightfold☆Fortune]` | `Eightfold ☆Fortune` | 2 | table 2 row 6 cell 1 |
| 101902 | `[Fanatic♡Jiangshi]` | `Fanatic♡ Jiangshi` | 2 | table 2 row 4 cell 1 |

The brief predicted several of these as "genuine renames" — `Run! Fun! Watergun!` shipping as
`[RUN! RUIN! LAUNCHER!]`. Measured at this hash, that rename is real and lives in the export's
`title` field (`Run! Fun! Watergun!`) while `title_en_gl` reads `[RUN! RUIN! LAUNCHER!]`, which is
what the parser stores — and both Tier A pages print the Global string, so it is **not** a
cross-source title conflict here; it is the one card of that pair that Game8 omits entirely (see
`100702` above). No card needed a title tie-break under rule 1: on every one of the 107, the client
string and the wiki's English title say the same thing at tier 1.

### English name spelling (1 trainee, 2 cards) — rule 3, no verdict change

`name_en` in the export reads **`TM Opera O`**; both Tier A pages read **`T.M. Opera O`**
(umamusu.wiki wikitable body rows 38 and 39; Game8 table 2 row 27 cell 1 and table 2 row 14 cell 3).
Both spellings are recorded here and the client-side string stays as stored, per rule 1's reasoning
about not editing data — but a Tier B field is not a client string in the way `title_en_gl` is, and
here it is the two-to-one outlier. This is a `umamusume` name question, not a card question: the
cards `101501` and `101502` are `two-source-confirmed` either way, and the alias row that would make
`T.M. Opera O` findable belongs to `umamusume_aliases` (FR-A-2). Task 9's store step has already run,
so adding it is a separate write against shared state, named for the owner rather than done here.

## What this says about KI-38

`KNOWN-ISSUES.md` KI-38: *"A card with the source's placeholder title is stored and rendered as a
real Global costume."* Cross-checked from this side, the placeholder population at this hash is
**exactly one card**: `103601`, `[unsigned]`, Global date `2026-06-18`, rarity 3. It is the only
title in the 107 that matches a placeholder shape (`unsigned|unconfirmed|placeholder|TBA|N/A|???`).

The finding that matters is that **both Tier A witnesses repeat it**: `umamusu.wiki` wikitable body
row 89 prints `unsigned` with the same date and the same rarity, and Game8's page prints the tile
`Air Shakur (unsigned)` at table 2 row 7 cell 1. So card `103601` comes out
**`two-source-confirmed`**, and KI-38's fix candidate (b) — *"flag the card `unconfirmed` so the
cross-check queue surfaces it"* — would not surface it: the queue is exactly the thing that has now
looked at it and found two independent pages agreeing with the export. The placeholder is upstream
and shared, not a GameTora artifact, so the remedy has to be the display path (candidate (a)), or an
admission rule that a title which is a placeholder is not a name. Not fixed here: Task 8 owns the
cross-check, not the admission rule. It also means `[unsigned]` is a **verified** row — any later
change that flags or hides it must not be justified by this file.

## Apply step (written, not run)

Task 8 produces the verdicts. Writing them into `character_cards.unconfirmed` is a second, separate
action, and Task 9's live fetch already ran without it — so this is the procedure, rehearsed on a
scratch database, **unrun against `database/database.sqlite`**. That file is another session's live
state and this branch's standing rule is never to create, mutate or `migrate:fresh` it; a write
against shared state is an owner action.

Preflight, on the database the owner means to apply to:

```sql
SELECT COUNT(*) FROM character_cards;                                   -- expect 107
SELECT COUNT(*) FROM character_cards WHERE unconfirmed = 1;              -- expect 0
SELECT card_id FROM character_cards WHERE is_manual = 1
   AND card_id IN (100103, 100702, 100802, 100902, 101303, 110001);      -- expect none; each hit is one card the guard below will not touch
```

Apply — the id list is the six verdicts above, copied from this file rather than recomputed by the
statement, so what the statement writes is exactly what was reviewed:

```sql
UPDATE character_cards
   SET unconfirmed = 1,
       updated_at  = CURRENT_TIMESTAMP
 WHERE is_manual = 0
   AND card_id IN (100103, 100702, 100802, 100902, 101303, 110001);
```

Expected: **0 flagged → 6 flagged** over 107 rows, `updated_at` moved on those 6 only. The
`is_manual = 0` term is the FR-B-4 guard: a card the Trainer corrected by hand is hers, and a
cross-check verdict does not overwrite her.

Rollback, same guard and same list:

```sql
UPDATE character_cards
   SET unconfirmed = 0,
       updated_at  = CURRENT_TIMESTAMP
 WHERE is_manual = 0
   AND card_id IN (100103, 100702, 100802, 100902, 101303, 110001);
```

Two properties worth knowing before running it:

- **A later fetch cannot clear these flags.** `StoreCharacterCards` deliberately leaves `unconfirmed`
  out of the columns it writes, and a card it skips is left alone. The flag survives a re-fetch by
  design, which is why it had to be earned by evidence rather than guessed.
- **The flag is not about ownership.** It says the card's existence, title, date and rarity were not
  attested twice. A card that re-parents keeps its flag, and clearing it means a new dated cross-check
  file, not an edit to this one.

### Rehearsal on scratch state

`database/scratch-catalog.sqlite` (gitignored by `/database/*.sqlite*`), migrated to HEAD, seeded
with the 68 trainees and all 107 cards from the **same** export body through the **same**
`StoreCharacterCards` action, then driven by the id list parsed out of
`docs/data/roster-crosscheck-table.md` — not retyped. The script lives in gitignored
`research-scratch/rehearse-apply.php` and aborts before touching anything if its connection resolves
to `database/database.sqlite`.

```
seeded trainees=68
store: created=107 updated=0 skipped=0
cards=107 flagged=0 manual=0

verdicts to flag from docs/data/roster-crosscheck-table.md: 6 -> 100103, 100702, 100802, 100902, 101303, 110001
before: cards=107 flagged=0 flagged-and-manual=0
set is_manual = 1 on card 100103 (a card the verdict table flags)
apply affected=5
after apply: cards=107 flagged=5 flagged-and-manual=0
card 100103 unconfirmed=false is_manual=true
re-apply affected=5 (idempotent)
after re-apply: cards=107 flagged=5 flagged-and-manual=0
re-fetch: created=0 updated=106 skipped=1
after re-fetch: cards=107 flagged=5 flagged-and-manual=0
undo affected=5
after undo: cards=107 flagged=0 flagged-and-manual=0
card 100103 left is_manual=true unconfirmed=false
```

Read line by line: `before: flagged=0` matches the preflight expectation. `apply affected=5`, not 6,
because the rehearsal deliberately makes one flagged card `is_manual = 1` first — the guard is
demonstrated, not asserted, and card `100103` stayed `unconfirmed=false` through it. The second apply
reports `affected=5` because SQLite counts the rows its `WHERE` found, not the rows whose value
moved; the effect is idempotent, since `flagged` stayed 5 and no row changed state. Re-running the
store over the same body updated 106 rows and skipped the manual one while **flagged stayed 5**: a
fetch cannot clear the verdicts. `undo` returned it to `flagged=0` and left `is_manual` alone, which
is the rollback path.

Against the real database the same statement with no manual rows in the list is 0 → 6.

## Standing

GameTora is **Tier B**; `umamusu.wiki` and Game8 are **Tier A**, per the ladder in
`docs/SOURCE-OF-TRUTH.md` §5:142-148 and the registry in `docs/UMAMUSUME_REFERENCE.md` §8 — and Tier
A is not infallible, which that same section records (`umamusu.wiki` has misassigned Grand Masters
bonus entries before, against two JP guides that agreed). §5:152 is the rule this file satisfies: a
Tier B claim becomes app data only on A- or S-tier confirmation.

This is a **dated observation**: 107 cards, 68 trainees, hash `e9e9ee6d`, read 2026-09-29. The counts
are true of that day's bodies. A later reader who needs them must re-read the pages, not re-read this
file, because banner cycles move and the `N/A` cells in this table are the proof.
