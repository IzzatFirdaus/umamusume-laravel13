"""Apply the adversarial review's findings to assembled-body.v2.md -> v3.

Each patch must match its expected number of times or the run fails loudly,
so a silently-missed fix cannot ship.
"""

import re
from pathlib import Path

S = Path("D:/Projects/umamusume-laravel13/research-scratch")
src = (S / "assembled-body.v2.md").read_text(encoding="utf-8")

# (pattern, replacement, expected_matches, regex?, note)
P = [
    (r"\[local export, ", "[GameTora data export, ", 27, True, "relabel source"),
    (r"the local GameTora export", "the GameTora data export", 1, True, "relabel 2.0"),
    (r"a local export of the GameTora database", "a machine-readable export of the GameTora database", 0, True, "no-op guard"),
    # finding 17: phase split numbers
    ("103 courses land on exactly those values, the remaining 35 round differently by less than 0.1%",
     "57 courses land on exactly those three fractions, and all 138 are within 0.1% of them", 1, False, "f17"),
    # finding 18: disambiguate the spurtStart claim (both readings are true, say which)
    ("`spurtStart` inside phase 2 in all 138 courses",
     "`spurtStart` inside the course's own phase 2 window in all 138 courses; only 95 of 138 also sit at or above the mathematical two-thirds point", 1, False, "f18"),
    # finding 19: slope group and the union that closes
    ("24 tag it as uphill or downhill (13 courses are counted in both the final-corner and the slope groups, which is why the four figures do not sum to 138)",
     "37 tag it as uphill or downhill and 8 more carry a `final_straight` tag; the groups overlap, and between them they cover all 138 courses", 1, False, "f19"),
    # finding 20: zero-corner courses
    ("three Sprint layouts being a straight with none",
     "exactly one course (id 10301, distance code 1) being a straight with no corner segments at all", 1, False, "f20"),
    # finding 21: spurt-gated skill tally, stated with its method
    ("last spurt, 18 of 115 spurt-gated",
     "last spurt, 18 of the 121 skills whose condition text pins `running_style==4`", 1, False, "f21"),
    # finding 11: one name per effect id
    ("| Failure Rate Down effect | Support-card effect that shrinks the roll on a shared tile |",
     "| Failure Protection (export id 27) | Support-card effect that shrinks the roll on a shared tile |", 1, False, "f11"),
    # finding 12: impossible probability phrasing
    ("from 10% at G or F to 100% at A and above 100% at S",
     "from 10% at G or F to 100% at A, with S graded parents rolling extra guaranteed inheritance attempts rather than a chance above 100%", 1, False, "f12"),
    # finding 4: terminology map currency row cited the wrong items
    ("| Per-pull exchange currency | スカウトPt / サポートPt | Scout Points / Support Points | Same concept | `items.json` ids 1001, 110 |",
     "| Per-pull exchange currency | 育成ウマ娘交換Pt / サポートカード交換Pt | Trainee Exchange Points / Support Card Exchange Points | Same concept, different wording | [JP official news id 3457](https://umamusume.jp/news/detail?id=3457) and the Global Scout notice; neither string appears in `items.json`, whose ids 1001 and 110 are the Dream Team event currency and the old support-card level currency |", 1, False, "f4"),
    # finding 22: attribute the 137 / 97 counts to the file that holds them
    ("Longer projections exist in `en__foresight__predicted_releases.json` (137 future character banners and 97 future support banners, all `is_estimated: true`, running to 2029)",
     "Longer projections exist in `en/foresight/timeline` under `future_char_banners` (137) and `future_support_banners` (97), every one of them `is_estimated: true`, running to 2029. `en/foresight/predicted_releases` is a different file, holding `char_cards`, `scenarios` and `support_cards` with no estimate flag at all", 1, False, "f22"),
    # finding 15: do not read a 403 page as confirmation
    ("Confirmed as 2025, and specifically 2025-06-26",
     "SUPPORTED at 2025-06-26 by the export minimum across 105 cards; the official page was not readable (HTTP 403), so this is one readable source plus an indexed title rather than a quoted announcement", 1, False, "f15"),
    # finding 24: column header named the wrong field
    ("| Band | `distance_type` | Course metres in export |",
     "| Band | `course.distance` (equivalent to the `distance_type` condition key in `skills.json`) | Course metres in export |", 1, False, "f24"),
    # finding 9: the placeholder item must not sit under "officially announced"
    ("4. Story event record id 1057, 2026-09-30 12:00 -> 2026-10-13 11:59 JST.",
     "4. `[DATAMINE]` Story event record id 1057, 2026-09-30 12:00 -> 2026-10-13 11:59 JST. Not an announcement: it is listed here because the window is dated, and it is excluded from the announced count in this subsection.", 1, False, "f9"),
    # finding 8: the Global Champions Meeting cycle description
    ("shows the full Aries-to-Pisces cycle twice",
     "runs Taurus Cup through Aries Cup (ids 1 to 12) and then restarts at Taurus Cup through Scorpio Cup (ids 13 to 19), which is 19 records rather than two complete 12-sign cycles", 1, False, "f8a"),
    ("repeats the Aries to Pisces cycle once per year",
     "restarts the zodiac series each year without completing it, 19 records spanning two partial cycles", 1, False, "f8b"),
    # finding 10: "current" claims whose windows had closed
    ("The current [Global] Champions Meeting disables exactly the three course-factor lines",
     "The [Global] Champions Meeting edition announced 2026.09.19 disables exactly the three course-factor lines", 1, False, "f10b"),
    # finding 23: the re-check note named files that do not carry the flag
    ("Rebuild the tables from the exports (`gacha__char_banners.json`, `gacha__support_banners.json`) and keep the `is_estimated` flag",
     "Rebuild the tables from the exports (`gacha/char_banners`, `gacha/support_banners`); the `is_estimated` flag lives only in the `en/foresight/timeline` records, so keep it there rather than looking for it in the banner files", 1, False, "f23"),
    # finding 3: conflict-log pointers that pointed at unrelated rows
    ("The 1400 m boundary is contested: see Conflict Log rows 1 and 2.",
     "The 1400 m boundary is contested: see Conflict Log row 7.", 1, False, "f3-123"),
    ("list \"Runaway\" beside the four styles. See Conflict Log row 3.",
     "list \"Runaway\" beside the four styles. See Conflict Log row 9.", 1, False, "f3-150"),
    ("one wiki labels 追込 \"Runner\" (conflict row 5)",
     "one wiki labels 追込 \"Runner\" (conflict row 25)", 1, False, "f3-609"),
    ("Logged as a date conflict, see Source Conflict Log row 1",
     "Logged as a date conflict, see Source Conflict Log row 34", 1, False, "f3-1164"),
    ("a contradictory 97% for ★/R (see Source Conflict Log row 2)",
     "a contradictory 97% for ★/R (see Source Conflict Log row 35)", 1, False, "f3-1328"),
    # finding 6: apply the log's own ruling instead of leaving it stale
    ("**End Date:** ❌ UNVERIFIED: no current source found. Last known: the 2026-09-24 22:00 UTC notice states availability without a closing time.",
     "**End Date:** 2026-09-28 21:59 UTC, closing the first window. The 2026-09-24 22:00 UTC notice states availability, and the closing time comes from the same notice read in full for 1.6.9; Conflict Log row 30 rules the earlier `❌ UNVERIFIED` closed.", 1, False, "f6-end"),
    ("**Key Rewards:** ❌ UNVERIFIED: notice body was not captured in this session beyond the availability statement.",
     "**Key Rewards:** Trainee Umamusume Star Pieces, Monies, and Support Points, from the same full read of the notice recorded in 1.6.9.", 1, False, "f6-rewards"),
    ("**Status:** Active (single official domain; see Source Conflict Log row 5)",
     "**Status:** Active (two domains: [Global official news 1058](https://umamusume.com/news/1058/) plus the [game8.co Transfer Request guide](https://game8.co/games/Umamusume-Pretty-Derby/archives/554781); see Source Conflict Log rows 26, 27, 30 and 38)", 1, False, "f14-tr"),
    # finding 14: Masters Challenge and Training Pass second domains
    ("**Source:** [S] `https://umamusume.jp/news/detail?id=3363` (2026-09-26 12:00, read 2026-09-27 02:05 JST); [S] news index `umamusume.jp/news/?t=game` same stamp",
     "**Source:** [S] [JP official news id 3363](https://umamusume.jp/news/detail?id=3363) (2026-09-26 12:00, read 2026-09-27 02:05 JST); [A] [Game8 JP Masters Challenge guide, 2026-09-11](https://game8.jp/umamusume/584950); [A] [Kamigame 第9回 strategy page, 2026-08-01](https://kamigame.jp/umamusume/page/296436114980404472.html)", 1, False, "f14-mc-src"),
    ("**Status:** Active (single official domain; no second-domain confirmation found, see Source Conflict Log row 4)",
     "**Status:** Active, on three domains: the official notice plus Game8 JP (2026-09-11) and Kamigame (第9回, 2026-08-01). The opening date is still `❌ UNVERIFIED`; the second-domain gap that this line reported is closed. See Source Conflict Log row 37", 1, False, "f14-mc-status"),
    ("**Other active `[JP]` notice too thin for a block:** トレーニングパス (Training Pass), update notice 2026-09-24 12:00 JST [S] `https://umamusume.jp/news/?t=game` (detail id 3455). Season close time and reward contents: ❌ UNVERIFIED, published in-app only, no second-domain source found (Source Conflict Log row 4).",
     "**Other active `[JP]` item:** トレーニングパス (Training Pass), update notice 2026-09-24 12:00 JST, [S] [JP official news id 3455](https://umamusume.jp/news/detail?id=3455), corroborated on a second domain by [A] [GameWith training and premium pass reward list, 2026-09-01](https://gamewith.jp/uma-musume/article/show/437940), which enumerates the track split as スタンダード against プレミアム (800 yen) and names the reward categories ジュエル, 育成ガチャチケ, サポガチャチケ, アビリティPt, 虹の解放結晶片 and サポートカード交換チケット. Per-season quantities and the season close time stay `❌ UNVERIFIED`: they are published in-app. See Source Conflict Log row 33.", 1, False, "f14-tp"),
]

out = src
report = []
for pat, rep, want, is_rx, note in P:
    if is_rx:
        out, n = re.subn(pat, rep, out)
    else:
        n = out.count(pat)
        if n:
            out = out.replace(pat, rep)
    report.append((note, n, want, "OK" if n == want else "MISMATCH"))

(S / "assembled-body.v3.md").write_text(out, encoding="utf-8")
bad = [r for r in report if r[3] != "OK" and not (r[0] == "no-op guard" and r[1] == 0)]
for r in report:
    if r[3] != "OK":
        print("  %-14s got %d want %d" % (r[0], r[1], r[2]))
print("patches applied:", len(report) - len(bad), "of", len(report))
print("FAILURES:", len(bad))
print("lines:", out.count("\n") + 1)
for i, ln in enumerate(out.splitlines(), 1):
    if "research-scratch" in ln:
        print("  local path at %d: %s" % (i, ln[:200]))
for tok in ["[Both]", "[JP-Only]", "local export"]:
    print(tok, out.count(tok))
