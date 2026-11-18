# Source research: the game-mechanics corpus in docs/research-scratch

**Date:** 2026-11-18
**Scope:** every `.md` file under `docs/research-scratch/` - 19 top-level masters plus 17 files in the
`screenshot-notes/`, `screenshot-notes-extras/` and `screenshot-registry/` subdirectories. 36 files,
128,591 lines measured with a line count at the time of reading.
**Question:** which externally checkable claims does this corpus rest on, do they still hold against current
publishers and primary sources, and where is the corpus's own confidence label wrong, stale, or unsupported?

**Bottom line.** The corpus is mostly an internal record (consolidated audits, slice records, plans, screenshot
annotations), not a web-research document. The externally checkable part is small and concentrates in six files.
Every scenario date and three of the four scenario cap tables verify cleanly against current sources. Two claims
need care: the Trackblazer Global cap table is still not confirmed by any publisher for the post-2026-07-01 Global
client, and the Unity Cup facility-level mapping from team-rank letters is an inference with no external source
found. The hint-discount ladder is the opposite case: the web's guides disagree with each other, but the repo's own
client screenshots and its 2026-11-02 measurement outrank them, so the web must not be used to "correct" it.

---

## 1. Corpus shape and method

### 1.1 What is in the corpus

| Group | Files | Lines | What it is |
|---|---|---|---|
| Top-level masters | 19 | 126,003 | Verbatim consolidations of earlier documents, audits, plans, slice records, screenshot notes and imported run data |
| `screenshot-notes/` | 8 | 1,570 | Per-frame annotations of in-game screenshots, read 2026-10-03 |
| `screenshot-notes-extras/` | 3 | 62 | Three additional frame notes |
| `screenshot-registry/` | 6 | 956 | Machine-readable screenshot registry schema, coverage and reports |
| **Total** | **36** | **128,591** | |

Of the 126,003 top-level lines, an estimated 90 percent is repository history: inventories, quality-bar decisions,
slice verification records, plans, briefs and imported run logs. That material is not externally researchable and
is out of scope for web verification by construction. The game-knowledge layer is concentrated in:

- `REFERENCE-GUIDE-BUILD-CHAIN.md` (compiled 2026-09-27; mechanics drafts with source-conflict logs)
- `SCENARIO-PUBLISHER-REFERENCES.md` (Trackblazer and Unity Cup publisher guides, with a Disagreements section)
- `SKILLS-MECHANICS.md` (skill facts, hint levels, Global scope)
- `RACE-AND-SLICE-RESEARCH.md` (race calendar, release dates, scenario availability)
- `SUPPORT-CARDS.md` and `CHARACTERS-SOURCE.md` (support-card and character data mechanics)
- `DESIGN-CORPUS.md` (design decisions that quote game facts; it also carries the mirror rule, D-285)

### 1.2 Method

1. Profiled every file for provenance headings, date stamps and external URLs. 37 distinct hosts appear; 953
   GameTora links dominate, followed by Kamigame (286), the official Global site (265), the official JP site (260),
   Game8 (241) and GameWith (218).
2. Separated claims into three classes: (a) externally checkable game facts, (b) export-derived facts that can only
   be checked against the local data export, (c) internal repository records.
3. For class (a), fetched current pages from the publisher that owns the claim and, where possible, a second
   independent source. Publication or last-updated dates were recorded per page.
4. Compared the corpus's own confidence labels with what the fresh evidence supports, and left a claim open when
   the evidence did not settle it.
5. Did not use Reddit threads as evidence. They are cited nowhere below except as an explicitly labelled signal
   where a publisher is silent.

### 1.3 What "verified" means here

- **Verified** - two independent sources agree, or one primary/client source plus one corroborating publisher.
- **Corroborated** - one publisher and the corpus's own evidence agree; no second independent publisher found.
- **Constructed** - a derivation from a known base plus a delta; arithmetic is checked, the inputs are not.
- **Open** - the evidence does not settle the claim; the corpus's own caution stands.
- **Internal** - not checkable outside the repository; no web verdict is possible or claimed.

---

## 2. Source register

| # | Source | Type | Date on the page / record | Authority for | Access note |
|---|---|---|---|---|---|
| 1 | GameTora - Unity Cup | Database + guide | Last updated 2026-07-20 | Unity caps, base training values, mechanics | `robots.txt` allows crawling except named paths; no terms page found[19] |
| 2 | GameTora - Trackblazer | Database + guide | Last updated 2026-03-12 | Trackblazer grade points, shop, caps | Same[19] |
| 3 | GameTora - Our Grand Concert | Database + guide | Last updated 2026-07-22 | Grand Concert caps and mechanics | Same[19] |
| 4 | uma.guide - Trackblazer | Community strategy | Last updated 2026-04-29 | Trackblazer turn economy, objectives, unique levels | No robots/terms checked; read-only fetch |
| 5 | Game8 - Unity Cup Update Guide | Guide wiki | Content dated 2026-07-01; repo records page date 2026-07-07 | July 1 rework: caps, Extreme Spirit Bursts, S+ rank | No date printed in served HTML |
| 6 | Game8 - URA Finale Guide | Guide wiki | Content dated 2026-07-01 | URA 1400 caps and rework mechanics | No date printed in served HTML |
| 7 | Game8 - Trackblazer Guide | Guide wiki | Repo records page date 2026-08-25 | Objectives, coins, rivals | No cap table in the served text |
| 8 | Umamusume Wiki - Unity Cup | Community wiki | Release table: EN 2025-11-06, EN update 2026-07-01 | Dates, S/S+ events, team mechanics | No page date |
| 9 | Umamusume Wiki - Trackblazer | Community wiki | Release table: EN 2026-03-12; caps table | Dates, cap table | No page date; does not separate server for the cap table |
| 10 | Umamusume Wiki - URA Finale | Community wiki | Release table: EN 2025-06-26, EN update 2026-07-01 | Dates | No page date |
| 11 | all.gg - fan farming | SEO guide | Updated 2026-06-17 | Maiden gate, Top Star 240k, Legend 320k | Byline dated |
| 12 | Deltia's Gaming - fan classes | SEO guide | No date | Full 7-band fan ladder | Byline absent |
| 13 | Deltia's Gaming - skill hints | SEO guide | No date | Hint discount (10% per hint, caps 40%) | Byline absent; conflicts with 22 |
| 14 | Automaton West - Cygames interview | Games press | July 2026, quoting a 2026-07-04 official post | Independent Training launch 2026-07-22, scenario launch | Interview format |
| 15 | Game8 - Independent Training | Guide wiki | No date; page shows Nov 2026 content | 50-minute window, 10 priority skills, closed-app timer | No date printed |
| 16 | Official - Known Issues | Publisher | Entries to 2026-07-31 in served text; search index shows 2026-11-17 | Independent Training and Unity Cup issue existence | Client-rendered page; partial text served |
| 17 | Official - Trackblazer campaign | Publisher | 2026-03-09 | Trackblazer launch window | Static notice |
| 18 | Official - Terms of Use | Publisher | Effective 2025-06-24 | Content ownership and service terms | No robots.txt at the host (404) |
| 19 | GameTora - robots.txt | Site policy | Fetched 2026-11-18 | Crawl permissions | Disallows 8 paths only |
| 20 | bo3.gg - training guide | SEO guide | 2025-07-21 | Fan class names and first three thresholds | Byline dated |
| 21 | Official - titles and missions | Publisher | 2026-11-09 | Team Trials is a distinct mode; Unity Cup team races excluded from some missions | Static notice |
| 22 | Umamusume Wiki - Skills | Community wiki | No page date | Hint discount as "~8% per hint, max 40%" | Conflicts with 13 and the client |

**Authority ranking used.** Client evidence (screenshots, measured frames) > official publisher notices > database
publishers with page dates (GameTora) > community strategy guides with dates (uma.guide) > undated guide wikis
(Game8, Deltia's) > community wikis without page dates > Reddit. A lower tier never overturns a higher one; it can
only open a question.

---

## 3. Per-file disposition

| File | Content class | Externally checkable claims | Disposition |
|---|---|---|---|
| `AUDIT-AND-VERIFICATION.md` | Internal audit and verification ledger | Quotes game facts (e.g. Independent Training 50 min, 1.3M fans) | Internal. Its game facts are re-used below only where a primary source confirms them |
| `CATALOG-ROSTER-WORKSTREAM.md` | Data pipeline, roster cross-check | Global release dates and card availability | Export-derived; not re-fetched. Dates agree with the official Global launch (2025-06-26) |
| `CHARACTERS-SOURCE.md` | Source probe for the character profile block | Character/profile availability | Internal; no external claim made |
| `CONSOLIDATION-LOG.md` | Process record | None | Internal |
| `DESIGN-AND-MECHANICS-REQUESTS.md` | Requests for verified game values (mood colours) | Mood-pill colours and mood race effect | Client-derived in `UMAMUSUME_REFERENCE.md` §1.1.6; secondary publishers disagree (4%/2% vs 10%/5%), the client panel settles 4%/2% |
| `DESIGN-CORPUS.md` | Design decisions and corpus rules | Quoted mechanics; server terminology | Mostly internal. Carries D-285: a document citing this repository is a mirror, not corroboration - applied here |
| `DOCUMENTATION-INVENTORY-2026-09-30.md` | Inventory | None | Internal |
| `GOVERNANCE.md` | Quality bar, gate registry | None | Internal |
| `PLANS-AND-BRIEFS.md` | Plans and owner rulings | Independent Training facts; Global terminology | Internal record; its game facts agree with [14][15] |
| `PROCESS-PLANS.md` | Process plans | None | Internal |
| `RACE-AND-SLICE-RESEARCH.md` | Race calendar and slice research | Race availability, Global release dates, `unreleased_servers` | Export-derived. Scenario dates agree with [8][9][10] |
| `REFERENCE-GUIDE-BUILD-CHAIN.md` | Game mechanics compilation | Training, race, inheritance, scenario caps, server terminology | The main researchable file. Compiled 2026-09-27; carries its own conflict log and `UNVERIFIED` markers. Two claims re-checked below |
| `RESIDUAL-VERIFICATION-2026-11-18.md` | Slice verification, dated today | Trackblazer/URA terminal rulings; KI-114 selectors | Internal; already the newest verification layer for those slices |
| `SCENARIO-PUBLISHER-REFERENCES.md` | Embedded publisher guides | Trackblazer and Unity Cup mechanics, caps, values | High-value. Written against the post-2026-07-01 Unity Cup; its own Disagreements section is the right place for residual doubt |
| `SKILLS-MECHANICS.md` | Skill facts and audits | Hint levels, SP costs, Global scope | Mixed dates: the 2026-10-01 layer calls the hint curve unsettled; the 2026-11-02 client measurement supersedes it |
| `SLICE-RECORDS.md` | Slice verification records | None directly | Internal |
| `SUPPORT-CARDS.md` | Support-card mechanics | Effect values, hint output, export fields | Export-derived; no external re-fetch of card values in this pass |
| `TRAINING-RUN-IMPORTS.md` | Imported player run data | None | Internal; player data, not game facts |
| `UX-DELIVERABLES.md` | Unadopted UX specification | Game facts inside a UX spec | Internal and explicitly unadopted. It still says Independent Training is "unconfirmed on Global", which was true when written and is false since 2026-07-22 |
| `screenshot-notes/`, `-extras/`, `screenshot-registry/` (17 files) | Frame annotations and registry | Client strings and badges, e.g. "Hint Lv 2 / 20% OFF!" | First-party evidence. Not web-verifiable, but stronger than a guide for the strings it records |

---

## 4. Claim verification matrix

| Claim in the corpus | Corpus value | Fresh evidence | Verdict | Confidence |
|---|---|---|---|---|
| Global launch date | 2025-06-26 | Automaton: worldwide debut on 2025-06-26[14]; wiki release table agrees[10] | Verified | High |
| URA Finale Global date and rework | EN 2025-06-26; rework 2026-07-01 | Wiki release table: EN 2025-06-26, EN update 2026-07-01[10]; Game8: URA updated 2026-07-01, caps 1400[6] | Verified | High |
| URA caps | 1400 across all five stats | Game8 prints 1400 ×5 and "+200 stat caps"[6] | Verified | High |
| Unity Cup Global dates | EN 2025-11-06; rework 2026-07-01 | Wiki release table[8]; GameTora: released 2025-11-06, updated 2026-07-01[1]; Game8 says the same[5] | Verified | High |
| Unity Cup caps | 1300 Speed/Stamina/Power/Guts, 1800 Wit | GameTora cap table[1]; Game8 "+100 ... +600 Wit"[5] | Verified | High |
| Unity Cup base training values | Global: +2 SP rows; JP: +4/+5 SP rows | GameTora prints the two tables separately, same numbers[1] | Verified | High |
| Grand Concert Global date | 2026-07-22 | GameTora: released 2026-07-22 on Global[3]; Automaton: July 22 update[14] | Verified | High |
| Grand Concert caps | 1600 / 1300 / 1300 / 1500 / 1300 | GameTora cap table matches exactly[3] | Verified | High |
| Trackblazer Global date | 2026-03-12 | Wiki release table[9]; official campaign 2026-03-09 for the launch window[17] | Verified | High |
| Trackblazer caps | 1200 / 1900 / 1200 / 1200 / 1500 | GameTora prints the five figures but says Global is only *expected* to launch at 1200 flat[2]; wiki prints the same five with EN release listed, no server split[9] | **Open for Global-live status**; the five figures are corroborated, the Global application is not | Medium |
| Trackblazer grade points and shop coins | G1 100, G2 80, G3 60, OP 40, Pre-OP 20; coins 100/60/30/0 | GameTora[2], uma.guide[4] and Game8[7] agree | Verified | High |
| Fan class ladder | Bronze 5k, Silver 20k, Gold 50k, Platinum 100k, Star 160k, Top Star 240k, Legend 320k | Deltia's prints the exact seven bands[12]; all.gg corroborates Top Star 240k and Legend 320k[11]; bo3 corroborates the first three and Legend[20] | Verified | High |
| Maiden gate | Win the Debut or any Maiden before other races | all.gg: failing the Debut forces a Maiden before the advanced races[11] | Corroborated | Medium-High |
| Independent Training | Live 2026-07-22; 50-minute window; runs with the app closed; up to 10 priority skills; three settings | Automaton interview: arrives with the 2026-07-22 update, finishes automatically[14]; Game8: 50 minutes, timer runs with the app closed, 10 skills, agenda/focus/skills setup[15]; official known issues reference Independent Training errors[16] | Verified | High |
| Hint discount ladder | Lv1 10, Lv2 20, Lv3 30, Lv4 35, Max 40 | Secondary guides conflict: Deltia's says 10% per hint, cap 40[13]; the wiki says ~8% per hint, max 40[22]. The repo's own frame notes record the client badges "Hint Lv 2 / 20% OFF!" and "Hint Lv 1 / 10% OFF!" | **Client evidence wins**; the ladder is corroborated by first-party frames and a 2026-11-02 measurement, not by the web | High for the repo value; low for any web "correction" |
| Unity Cup team rank S / S+ | S at overall rank, S+ adds a hint, no extra facility rung | GameTora[1], Game8[5] and the wiki[8] all describe S and S+ | Verified for S/S+ | High |
| Unity Cup facility level per team-rank letter | G/F = 1, D/E = 2, B/C = 3, A = 4, S = 5 | No fetched source prints the letter-to-facility mapping | **Constructed**; not externally verified | Low-Medium |

---

## 5. Server and version timeline

The corpus uses `[Global]` and `[JP]` tags throughout and forbids merging them. The dates below are the ones a
reader must apply before taking any number from any master.

| Date (Global) | Event | Consequence for the corpus |
|---|---|---|
| 2025-06-24 | Official Terms of Use effective[18] | Content ownership and reuse constraints apply to every quoted string |
| 2025-06-26 | Global launch; URA Finale available | URA is the baseline scenario |
| 2025-11-06 | Unity Cup released on Global[1][8] | Unity guides written before 2025-11-06 describe JP only |
| 2026-03-12 | Trackblazer released on Global[9][17] | Trackblazer guides dated before this are JP-only |
| 2026-07-01 | Global rework of URA and Unity Cup[5][6][10] | Any URA or Unity cap, SP or base-value table older than this is stale unless it says "post-update" |
| 2026-07-22 | Our Grand Concert released; Independent Training added[3][14] | Grand Concert and Independent Training are `[Global]` from this date; earlier text calling them unconfirmed is historical |
| 2026-11-09 | New titles and missions; Team Trials missions added[21] | Team Trials is a separate mode from Unity Cup; do not merge their rank ladders |
| 2026-11-18 | This pass | All measurements are dated to this day |

**Version-compatibility rule that follows.** A claim's confidence is bounded by the later of its source date and
the scenario update date. A pre-2026-07-01 Unity number is not "wrong"; it is pre-rework and must be labelled. The
corpus already does this in `SCENARIO-PUBLISHER-REFERENCES.md`, but `REFERENCE-GUIDE-BUILD-CHAIN.md` embeds the
older layer verbatim, so a reader who opens the build chain first will see pre-rework values without the newer
header.

---

## 6. Findings

### F1. Only a sixth of the corpus is web-researchable

36 files, 128,591 lines, but the externally checkable game claims sit in six files. The rest is repository history
and cannot be "researched" on the web. A future pass that tries to verify every line will spend its budget on
audits and slice records that no publisher could ever confirm. The right unit is the claim, not the file.

### F2. Scenario dates and three cap tables verify cleanly

URA 1400, Unity 1300/1800 and Grand Concert 1600/1300/1300/1500/1300 all match current publishers.[1][3][6] The
release dates match too: URA and Unity in the wiki tables,[8][10] Trackblazer in the official campaign and the
wiki,[9][17] Grand Concert in GameTora.[3] No action is owed on those rows.

### F3. Trackblazer's Global cap remains the one unresolved numeric

GameTora's Trackblazer page is stamped 2026-03-12, the launch day, and explicitly says Global is only *expected*
to launch at 1200 across the board.[2] The wiki prints 1200/1900/1200/1200/1500 with the EN release date in the
same infobox but no server qualifier.[9] No source fetched for this pass prints a post-2026-07-01 Global
Trackblazer cap. The corpus already holds this at MEDIUM and says one look at the live client settles it
(`UMAMUSUME_REFERENCE.md` §2.7). This pass found no evidence that raises it, and one reason not to: the only
page that discusses Global explicitly calls the higher numbers non-Global. **Keep the config value; keep the doubt
visible.** Do not upgrade the label to Verified on the strength of the wiki alone.

### F4. The fan ladder is fully verified, including the two intermediate bands earlier passes lacked

The exact seven bands are printed by an undated guide[12] and the key thresholds are corroborated by two dated
guides.[11][20] The two intermediate bands, Platinum and Star, now have a printed source rather than only an
arithmetic inference.

### F5. Independent Training is verified on every material point

Launch 2026-07-22, 50-minute duration, timer runs while the app is closed, up to 10 priority skills, and the
Normal/Independent choice at start.[14][15] Official known issues also reference the mode and its errors.[16] The
corpus's own `AUDIT-AND-VERIFICATION.md` claim (50 min, notice 100087) is consistent; the notice itself was not
retrievable in this pass, so the citation chain rests on the press interview and the guide rather than the notice.

### F6. The hint ladder is a case where the web is the weaker source

Two secondary sources give two different curves - 10% per hint to 40%[13] and ~8% per hint to 40%[22] - while the
repo carries a third, 10/20/30/35/40. The repo's screenshot annotations record the client printing "Hint Lv 1 / 10%
OFF!" and "Hint Lv 2 / 20% OFF!", and `config/uma.php:509-529` records the full ladder as read off 68 `[Global]`
Learn-screen frames on 2026-11-02. That is first-party evidence; the guides are not. **A future fact-check must not
"fix" the ladder from the web.** The older `SKILLS-MECHANICS.md` passage calling the curve unsettled predates the
measurement; the newer layer supersedes it and the document should say so once, at the top of its hint section.

### F7. Team Rank has two ladders, and only one is sourced

Unity Cup's S and S+ are described by three sources.[1][5][8] The facility-level mapping from letters (G/F = 1,
D/E = 2, B/C = 3, A = 4, S = 5) is not printed by any fetched source; it is an inference, and the corpus uses it as
if it were a fact. Separately, Team Trials is its own mode with its own missions, added 2026-11-09;[21] the two ladders must not be merged. `ADR-0034` is careful about S+ mapping to no facility
rung, but the lower-letter mapping deserves the same label.

### F8. Page dates are missing from the two most-cited guide sites

Game8 pages do not print a last-updated date in the served HTML; the repo records page dates in
`UMAMUSUME_REFERENCE.md` (2026-07-07 for 545572, 2026-08-25 for 580723). Deltia's pages carry no date at all.
A citation that says "Game8" without the repo-recorded date is weaker than it looks. Prefer the content date (the
July 1 rework) or the repo-recorded page date, and never infer freshness from the URL.

### F9. Source legality is favourable for the one host the app fetches, and unexamined for the rest

`config('uma.sources')` contains 13 keys, all GameTora. GameTora's `robots.txt` disallows eight paths and allows
the rest.[19] No terms page was found at the obvious paths. The official Global site has no `robots.txt` (404)[23] and
publishes Terms of Use effective 2025-06-24 that assert Cygames copyright over the content.[18] The corpus cites
Kamigame, GameWith and Game8 heavily but the app never fetches them. So the existing source package is adequate for
the fetch engine as configured; adding any new host still needs its own robots/terms note.

### F10. The corpus already knows its own mirror risk, and this pass confirms it

`DESIGN-CORPUS.md` D-285 states that a document citing this repository is a mirror, not corroboration. No external
source fetched here cites the repository, so the contamination is internal, not external: the same numbers repeat
across `REFERENCE-GUIDE-BUILD-CHAIN.md`, `SCENARIO-PUBLISHER-REFERENCES.md` and `UMAMUSUME_REFERENCE.md` because
they were consolidated from each other. Counting those repetitions as independent confirmation would be wrong.

### F11. One archived UX document is stale on a feature that now exists

`UX-DELIVERABLES.md` says "Independent Training (unconfirmed on Global)". That was true before 2026-07-22 and is
false now.[14][15] The file is a verbatim archive and should not be rewritten; the stale line should be read as a
timestamp, and any live document that quotes it should carry the newer date.

---

## 7. Comparing the source approaches

| Approach | Strength | Weakness | Correct use |
|---|---|---|---|
| In-game client frames (screenshot notes) | First-party; exact strings and badges | Sparse; requires a capture corpus; cannot cover every screen | Settle UI copy, percentages and labels the client prints |
| Official notices (umamusume.com) | Authoritative dates, feature launches, issue states | Client-rendered pages are hard to fetch; notices are announcements, not mechanics tables | Dates, feature existence, known issues |
| GameTora | Page-stamped, structured tables, broad coverage | Launch-day hedges; server split not always inside the table | Numeric tables, with the page date quoted |
| uma.guide | Dated, strategy-first, honest about uncertainty | Fewer exact numbers; community-authored | Strategy and turn economy, not cap tables |
| Game8 | Fast update summaries, clear prose | No visible page date; community readers distrust parts of it | Corroboration and patch summaries, never sole authority |
| Umamusume Wiki | Structured infoboxes, release tables, S/S+ detail | No page date; JP-centric tables; server split missing on some pages | Cross-check and dates |
| SEO guides (Deltia's, bo3, all.gg) | Complete lists, readable tables, sometimes dated | Undated or stale; copied across sites | Fan thresholds and simple ladders only, with a second source |
| Reddit | Early signal of a change | Anecdote; deleted threads; no method | Open a question; never close one |

**The practical rule.** For a numeric table, GameTora with its page date. For a date, the wiki plus an official
notice. For UI strings, the client frames. For a strategy claim, uma.guide. For anything where these disagree, keep
both readings and label the difference rather than picking the more convenient one.

---

## 8. Assumptions and limits

- **No live client access in this pass.** The strongest evidence remains the repo's own frame captures; this pass
  could not take new frames. The Trackblazer Global cap therefore stays open.
- **No Japanese-language source was re-read.** JP values are taken from the corpus's translations and from
  GameTora's server-split tables, not from a fresh JP client check.
- **Page dates are not uniform.** Game8 prints none; Deltia's prints none; the wikis print release dates but no
  page date. "Last updated" on GameTora and uma.guide is the only reliable page stamp in the set.
- **The official news pages are client-rendered.** `news/155` served partial text; `news/100087` did not render.
  The Independent Training launch is therefore sourced from press and a guide, not from the notice text itself.
- **The exact number of frames behind each screenshot note was not re-counted.** The notes say 2026-10-03 and the
  registry says 1,159 PNGs; those are static reads, not new measurements.
- **No support-card or character values were re-fetched.** Those tables are export-derived; checking them needs the
  export, not the web, and was outside this pass.

---

## 9. Actionable follow-ups

Each item names the owning file and a verification command. No item asks for a rewrite of an archive.

| # | Action | File | Verification |
|---|---|---|---|
| A1 | Add a dated note to the Trackblazer cap line saying the five figures are corroborated but the *Global-live* status is still open, and naming both sources | `SCENARIO-PUBLISHER-REFERENCES.md` around line 526 | `grep -n "expected to launch" docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` |
| A2 | Keep the config value for Trackblazer caps; do not change it until a client check or patch note settles it | `config/uma.php` `scenarios.trackblazer.cap_bonus` | `php artisan tinker --execute="echo json_encode(config('scenarios.scenarios.trackblazer.cap_bonus'));"` |
| A3 | Label the team-rank letter-to-facility mapping as inferred, and state that Team Trials is a separate ladder | `docs/adr/0034-team-rank-stored-current-state.md` or the `TeamPanelBuilder` docblock | `python tools/adr-check.py docs/adr` |
| A4 | Add one line at the top of the hint section saying the 2026-11-02 client measurement supersedes the earlier "unsettled" position | `SKILLS-MECHANICS.md` §2 | `python tools/md_fence_check.py docs/research-scratch/SKILLS-MECHANICS.md` |
| A5 | Record GameTora's robots policy and the absence of a terms page in the source package for the 13 `gametora-*` keys | `config/uma.php` source comments | `php artisan config:show uma.sources` |
| A6 | Do not edit the archived UX spec; if a live doc quotes "unconfirmed on Global", replace it with the 2026-07-22 date | wherever the line is quoted | `git grep -n "unconfirmed on Global"` |
| A7 | Prefer content dates over page names when citing Game8; the page date exists only in the repo's own record | `UMAMUSUME_REFERENCE.md` §2.7 | `git grep -n "580723"` |

**No code change is justified by this pass.** The verified rows already match the shipped config; the one open row
is open for lack of a primary measurement, not because the value is known to be wrong.

---

## 10. What the corpus should be trusted for

- **Trust:** scenario dates and order, the URA and Unity cap tables, the Grand Concert cap table, the fan ladder,
  the maiden gate, Independent Training's shape and launch, and the client-printed hint badges.
- **Trust with its own caveat:** Trackblazer's cap table (five figures corroborated, Global application open), the
  team-rank facility mapping (inferred), and any pre-2026-07-01 Unity or URA number (pre-rework).
- **Do not use as game evidence:** slice records, audits, plans, UX deliverables, imported run logs. They are the
  repository's memory, not the game's.

---

## Sources

[1] https://gametora.com/umamusume/unity-cup — GameTora - Unity Cup Scenario (last updated 2026-07-20)
[2] https://gametora.com/umamusume/trackblazer — GameTora - Trackblazer Scenario (last updated 2026-03-12)
[3] https://gametora.com/umamusume/our-grand-concert — GameTora - Our Grand Concert Scenario (last updated 2026-07-22)
[4] https://uma.guide/guides/trackblazer — uma.guide - Trackblazer Guide (last updated 2026-04-29)
[5] https://game8.co/games/Umamusume-Pretty-Derby/archives/545572 — Game8 - Unity Cup Update Guide (July 1 2026 update)
[6] https://game8.co/games/Umamusume-Pretty-Derby/archives/536520 — Game8 - URA Finale Scenario Guide (July 1 2026 update)
[7] https://game8.co/games/Umamusume-Pretty-Derby/archives/580723 — Game8 - Trackblazer Scenario Guide
[8] http://umamusu.wiki/Game:Unity_Cup — Umamusume Wiki - Unity Cup (release table)
[9] https://umamusu.wiki/Game:Trackblazer — Umamusume Wiki - Trackblazer (release table and caps)
[10] https://umamusu.wiki/Game:The_Beginning:_URA_Finale — Umamusume Wiki - URA Finale (release table)
[11] https://all.gg/news/struggling-for-g1-races-heres-how-to-farm-more-fans-in-umamusume — all.gg - fan farming (updated 2026-06-17)
[12] https://deltiasgaming.com/umamusume-pretty-derby-all-fans-and-classes-explained — Deltia's Gaming - all fan classes
[13] https://deltiasgaming.com/umamusume-pretty-derby-how-to-get-more-skill-hints — Deltia's Gaming - skill hints
[14] https://automaton-media.com/en/news/umamusume-1-5-year-anniversary-interview-with-cygames-the-difficult-undertaking-of-bringing-the-independent-training-mode-overseas-so-early-brand-new-career-scenario-and-more — Automaton West - Cygames interview (July 2026)
[15] https://game8.co/games/Umamusume-Pretty-Derby/archives/612143 — Game8 - Independent Training Guide
[16] https://umamusume.com/news/155 — Official - List of Known Issues
[17] https://umamusume.com/news/200007 — Official - Trackblazer release support event (2026-03-09)
[18] https://umamusume.com/terms — Official - Terms of Use (effective 2025-06-24)
[19] https://gametora.com/robots.txt — GameTora - robots.txt (fetched 2026-11-18)
[20] https://bo3.gg/games/articles/umamusume-pretty-derby-complete-guide-to-training — bo3.gg - training guide (2025-07-21)
[21] https://umamusume.com/news/1168 — Official - titles and missions (2026-11-09)
[22] https://umamusu.wiki/Game:Skills — Umamusume Wiki - Skills
[23] https://umamusume.com/robots.txt — Official - robots.txt (404 on 2026-11-18)
