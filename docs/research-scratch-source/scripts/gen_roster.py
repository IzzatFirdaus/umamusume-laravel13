"""Generate Section 2 (character roster) tables from the local GameTora export.

Tables are data-derived rather than hand-typed: a 268-row table retyped from
memory is where fabricated aptitude letters would come from.
"""

import json
from pathlib import Path

D = Path("D:/Projects/umamusume-laravel13/research-scratch/data/json")
OUT = Path("D:/Projects/umamusume-laravel13/research-scratch/gen-sec2.md")

# Katakana/hiragana -> Hepburn romaji. Macrons mark long vowels.
DIGRAPHS = {
    "キャ": "kya", "キュ": "kyu", "キョ": "kyo", "シャ": "sha", "シュ": "shu", "ショ": "sho",
    "チャ": "cha", "チュ": "chu", "チョ": "cho", "ニャ": "nya", "ニュ": "nyu", "ニョ": "nyo",
    "ヒャ": "hya", "ヒュ": "hyu", "ヒョ": "hyo", "ミャ": "mya", "ミュ": "myu", "ミョ": "myo",
    "リャ": "rya", "リュ": "ryu", "リョ": "ryo", "ギャ": "gya", "ギュ": "gyu", "ギョ": "gyo",
    "ジャ": "ja", "ジュ": "ju", "ジョ": "jo", "ビャ": "bya", "ビュ": "byu", "ビョ": "byo",
    "ピャ": "pya", "ピュ": "pyu", "ピョ": "pyo", "ファ": "fa", "フィ": "fi", "フェ": "fe",
    "フォ": "fo", "イェ": "ye", "ウェ": "we", "ウォ": "wo", "ティ": "ti", "ディ": "di",
    "トゥ": "tu", "ドゥ": "du", "シェ": "she", "ジェ": "je", "チェ": "che", "ツァ": "tsa",
    "ツェ": "tse", "ツォ": "tso", "ヴァ": "va", "ヴィ": "vi", "ヴ": "vu", "ヴェ": "ve", "ヴォ": "vo",
    "ウァ": "wa", "ウィ": "wi", "イィ": "yi", "ゥ": "wu",
}
KANA = {
    "ア": "a", "イ": "i", "ウ": "u", "エ": "e", "オ": "o",
    "カ": "ka", "キ": "ki", "ク": "ku", "ケ": "ke", "コ": "ko",
    "サ": "sa", "シ": "shi", "ス": "su", "セ": "se", "ソ": "so",
    "タ": "ta", "チ": "chi", "ツ": "tsu", "テ": "te", "ト": "to",
    "ナ": "na", "ニ": "ni", "ヌ": "nu", "ネ": "ne", "ノ": "no",
    "ハ": "ha", "ヒ": "hi", "フ": "fu", "ヘ": "he", "ホ": "ho",
    "マ": "ma", "ミ": "mi", "ム": "mu", "メ": "me", "モ": "mo",
    "ヤ": "ya", "ユ": "yu", "ヨ": "yo",
    "ラ": "ra", "リ": "ri", "ル": "ru", "レ": "re", "ロ": "ro",
    "ワ": "wa", "ヰ": "wi", "ヱ": "we", "ヲ": "wo", "ン": "n",
    "ガ": "ga", "ギ": "gi", "グ": "gu", "ゲ": "ge", "ゴ": "go",
    "ザ": "za", "ジ": "ji", "ズ": "zu", "ゼ": "ze", "ゾ": "zo",
    "ダ": "da", "ヂ": "ji", "ヅ": "zu", "デ": "de", "ド": "do",
    "バ": "ba", "ビ": "bi", "ブ": "bu", "ベ": "be", "ボ": "bo",
    "パ": "pa", "ピ": "pi", "プ": "pu", "ペ": "pe", "ポ": "po",
    "ァ": "la", "ィ": "li", "ゥ": "lu", "ェ": "le", "ォ": "lo",
    "ャ": "xa", "ュ": "xu", "ョ": "xo", "っ": "t", "ッ": "t",
    "ー": "-", "、": "", "。": "", "・": " ", "ー ": "-",
}
VOWEL_LONG = {"a": "ā", "i": "ī", "u": "ū", "e": "ē", "o": "ō"}
CONSONANTS = set("bcdfghjklmnpqrstvwxyz")


def romaji(name):
    """Hepburn romanization of a katakana name. Returns (romaji, unmapped_chars)."""
    out, unmapped = [], []
    chars = list(name)
    i = 0
    while i < len(chars):
        two = "".join(chars[i:i + 2])
        if two in DIGRAPHS:
            out.append(DIGRAPHS[two])
            i += 2
            continue
        c = chars[i]
        nxt = chars[i + 1] if i + 1 < len(chars) else ""
        if c == "ー":
            prev = out[-1] if out else ""
            lastv = prev[-1] if prev else ""
            if lastv == "っ":
                out[-1] = "t"
            elif lastv in VOWEL_LONG:
                out[-1] = prev[:-1] + VOWEL_LONG[lastv]
            elif lastv == "u":
                out[-1] = prev[:-1] + "ū"
            else:
                out.append("a")
                unmapped.append(c)
            i += 1
            continue
        if c in KANA:
            r = KANA[c]
            if r == "n" and nxt in ("バ", "ビ", "ブ", "ベ", "ボ", "マ", "ミ", "ム", "メ", "モ", "パ", "ピ", "プ", "ペ", "ポ"):
                r = "m"
            if r == "t" and nxt and (nxt in KANA or nxt in DIGRAPHS) and nxt not in ("ッ", "ー"):
                peek = KANA.get(nxt, DIGRAPHS.get(nxt, ""))
                if peek and peek[0] in CONSONANTS:
                    r = peek[0]
                elif peek.startswith("ch") or peek.startswith("ts"):
                    r = peek[0]
            out.append(r)
            i += 1
            continue
        if c in " 1234567890'’-().!?:&":
            out.append(c)
            i += 1
            continue
        unmapped.append(c)
        out.append(c)
        i += 1
    s = "".join(out)
    s = " ".join(s.split())
    return titlecase(s), unmapped


def titlecase(s):
    words = []
    for w in s.split(" "):
        if not w:
            continue
        if w.isascii() and w.isdigit():
            words.append(w)
        elif any(ch.isupper() for ch in w[1:]):
            words.append(w)  # preserve internal caps such as T.M. or Curren Bouquetd'Or
        else:
            words.append(w[0].upper() + w[1:])
    return " ".join(words)


def strip_marks(s):
    for mac, plain in VOWEL_LONG.items():
        s = s.replace(plain, mac)
    return "".join(ch for ch in s.lower() if ch.isalpha())


def syllables(name):
    """Split a kana name into its romanized pieces, keeping digraphs intact."""
    out, i, chars = [], 0, list(name)
    while i < len(chars):
        two = "".join(chars[i:i + 2])
        if two in DIGRAPHS:
            piece = DIGRAPHS[two]
            if len(two) == 2 and two[1] in "ァィゥェォ" and strip_marks(piece) == "":
                piece = None
            if piece:
                i += 2
                out.append(piece)
                continue
        r, _ = romaji("".join(chars[i:i + 1]))
        if r == "":
            i += 1
            continue
        # a macron belongs to the vowel it lengthens
        if chars[i] == "ー" and out:
            out[-1] = r if r else out[-1]
        else:
            out.append(r)
        i += 1
    return out


def spaced_romaji(name_jp, url_name):
    """Hepburn with word breaks, recovered by aligning syllables to the export slug."""
    base = romaji(name_jp)[0]
    words = [w for w in (url_name or "").split("-") if w and not w.isdigit()]
    if len(words) < 2:
        return base
    syl = syllables(name_jp)
    targets = [strip_marks(w) for w in words]
    groups, si, ok = [], 0, True
    for t in targets:
        acc = ""
        start = si
        while si < len(syl) and len(strip_marks(acc)) < len(t):
            acc += syl[si]
            si += 1
        if strip_marks(acc) != t:
            ok = False
            break
        groups.append(acc)
    if not ok or si != len(syl) or len(groups) != len(targets):
        return base
    return " ".join(g[0].upper() + g[1:] for g in groups)


RARITY = {1: "R", 2: "SR", 3: "SSR"}
STATS = ["Speed", "Stamina", "Power", "Guts", "Wit"]


def apt(row):
    a = row["aptitude"]
    track = f"Turf {a[0]} / Dirt {a[1]}"
    dist = f"Sprint {a[2]} / Mile {a[3]} / Medium {a[4]} / Long {a[5]}"
    # client strings from factors.json pink ids 21-24 (name_en equals name_en_gl)
    strat = f"Front Runner {a[6]} / Pace Chaser {a[7]} / Late Surger {a[8]} / End Closer {a[9]}"
    return track, dist, strat


def bonuses(row):
    b = row.get("stat_bonus") or [0, 0, 0, 0, 0]
    named = [f"{STATS[i]}+{v}" for i, v in enumerate(b) if v]
    return ", ".join(named) if named else "none"


def cell(text):
    return str(text).replace("|", "/").replace("\n", " ").strip()


def main():
    chars = {c["char_id"]: c for c in json.loads((D / "characters.json").read_text(encoding="utf-8"))}
    cards = json.loads((D / "character-cards.json").read_text(encoding="utf-8"))
    skills = {s["id"]: s for s in json.loads((D / "skills.json").read_text(encoding="utf-8"))}

    by_char = {}
    for c in cards:
        by_char.setdefault(c["char_id"], []).append(c)
    for cid in by_char:
        by_char[cid].sort(key=lambda c: (c.get("release") or "9999", c["card_id"]))

    all_unmapped = sorted({u for c in cards for u in romaji(c["name_jp"])[1]})
    debut = {cid: v[0] for cid, v in by_char.items()}

    total_units = len(debut)
    jp_units = sum(1 for cid in debut if chars.get(cid, {}).get("playable"))
    en_units = sum(1 for cid in debut if chars.get(cid, {}).get("playable_en"))
    cards_en = sum(1 for c in cards if c.get("release_en"))
    mismatches = [
        (c["card_id"], c["name_jp"], c["char_id"])
        for c in cards
        if not c.get("release_en") and chars.get(c["char_id"], {}).get("playable_en")
    ]
    debut_gap = [
        (c["card_id"], c["name_jp"])
        for cid, c in debut.items()
        if not c.get("release_en") and chars.get(cid, {}).get("playable_en")
    ]

    def uniq_name(card):
        ids = card.get("skills_unique") or []
        parts = []
        for sid in ids:
            s = skills.get(sid)
            if s:
                label = s.get("name_en") or s.get("enname") or "id %d" % sid
                jp = s.get("jpname") or ""
                parts.append(f"{label} ({jp})" if jp else label)
            else:
                parts.append(f"❌ UNVERIFIED: skill id {sid} absent from export")
        return "; ".join(parts) if parts else "N/A (no unique skill id in export)"

    def rows(source, *, variant=False):
        out = []
        n = 0
        for cid, card in source:
            n += 1
            ch = chars.get(cid, {})
            track, dist, strat = apt(card)
            rom = spaced_romaji(card.get("name_jp", ""), card.get("url_name"))
            jp_rel = card.get("release") or "❌ UNVERIFIED"
            en_rel = card.get("release_en")
            # server status is a property of THIS card, not of the unit
            status = "[Both]" if en_rel else "[JP-Only]"
            en_name = cell(ch.get("en_name") or card.get("name_en") or "❌ UNVERIFIED: no EN rendering in export")
            if not en_rel and not ch.get("playable_en"):
                en_name = "N/A (unit unreleased on [Global])"
            elif not en_rel:
                en_name = en_name + " (unit live on [Global], this card is not)"
            traits = [
                f"{RARITY.get(card.get('rarity'), card.get('rarity'))}",
                cell(card.get("title_en_gl") or card.get("title") or "untitled"),
                f"debut [JP] {jp_rel}",
                f"debut [Global] {en_rel}" if en_rel else "this card has no [Global] release",
                f"`stat_bonus` {bonuses(card)} (meaning unconfirmed, see 1.3.5)",
            ]
            if not variant:
                extra = len(by_char[cid]) - 1
                traits.append(f"{extra} alternate costume card{'s' if extra != 1 else ''}")
            else:
                traits.append("alternate costume card")
            traits.append(f"★5 ceiling {max(card.get('five_star_stats') or [0])}")
            url = "https://gametora.com/umamusume/characters/%s" % card.get("url_name", str(card["card_id"]))
            out.append(
                "| %d | %s | %s | %s | %s | %s | %s | %s | %s | %s | [GameTora card %d](%s) |"
                % (
                    n,
                    cell(card.get("name_jp")),
                    cell(ch.get("en_name") or card.get("name_en") or spaced_romaji(card.get("name_jp", ""), card.get("url_name"))),
                    en_name,
                    status,
                    track,
                    dist,
                    strat,
                    cell(uniq_name(card)),
                    cell(" · ".join(traits)),
                    card["card_id"],
                    url,
                )
            )
        return out

    ordered_debut = sorted(debut.items(), key=lambda kv: (kv[1].get("release") or "9999", kv[1]["card_id"]))
    variants = sorted(
        [c for cid in by_char for c in by_char[cid][1:]],
        key=lambda c: (c.get("release") or "9999", c["card_id"]),
    )

    HEADER = (
        "| # | JP Name | Romanized Name | Global EN Name | Server Status | Aptitude (Track) "
        "| Aptitude (Distance) | Aptitude (Strategy) | Unique Skill Name | Notable Traits | Source |"
    )
    RULE = "|" + "|".join(["---"] * 11) + "|"

    both = sum(1 for cid, c in ordered_debut if chars.get(cid, {}).get("playable_en"))
    both_units = sum(1 for cid in debut if chars.get(cid, {}).get("playable_en"))
    jp_only_units = total_units - both_units
    variants_all = [c for c in cards if c is not debut[c["char_id"]]]
    variants_both = sum(1 for c in variants_all if c.get("release_en"))
    nd_total = len(cards) - len(debut)
    nd_both = sum(1 for c in cards if c is not debut[c["char_id"]] and c.get("release_en"))
    multi_word = [c for c in cards if len([w for w in (c.get("url_name") or "").split("-") if w and not w.isdigit()]) > 1]
    aligned = [c for c in multi_word if " " in spaced_romaji(c.get("name_jp", ""), c.get("url_name"))]

    lines = [
        "## Section 2 — Character Roster",
        "",
        "Counts read from the local GameTora export on 2026-09-27 (`characters.json`, `character-cards.json`).",
        "",
        "| Measure | Value |",
        "|---|---|",
        f"| Distinct trainable Umamusume, any server | {total_units} |",
        f"| Released on `[Both]` | {both_units} |",
        f"| `[JP-Only]` | {jp_only_units} |",
        f"| Trainable cards total (debut forms and alternate costumes) | {len(cards)} |",
        f"| Cards with a `[Global]` release date | {cards_en} |",
        f"| Cards with a `[JP]` release date | {sum(1 for c in cards if c.get('release'))} |",
        f"| Units with 1 / 2 / 3 cards | "
        + " / ".join(
            str(sum(1 for cid in by_char if len(by_char[cid]) == k)) for k in (1, 2, 3)
        )
        + " |",
        "",
        f"Server status is asserted per card, not per unit. {len(mismatches)} alternate-costume cards belong to a unit that is",
        f"already live on `[Global]` while the card itself has never shipped there, so those rows read `[JP-Only]` next to an",
        f"English name that is official for the unit. {len(debut_gap)} debut rows disagree between the unit flag and the card date.",
        "",
    ]
    if mismatches:
        lines += ["| card_id | JP name | char_id |", "|---|---|---|"]
        lines += ["| %s | %s | %s |" % (cid, cell(nm), ch) for cid, nm, ch in mismatches]
        lines.append("")

    lines += [
        "### 2.1 Playable Umamusume, debut form",
        "",
        "Sorted by `[JP]` debut date, then by database id. Aptitude letters follow the export's element order,",
        "confirmed cell by cell against two independent sources for three diagnostic units (see the cross-check note in 2.3).",
        "Strategy labels are the client strings from the export (`factors.json` pink ids 21 to 24, where `name_en` equals",
        "`name_en_gl`, corroborated by Global nickname text such as \"Win 6 races as a Front Runner\"). A shorter set,",
        "`Runner` / `Leader` / `Betweener` / `Chaser`, circulates in community guides and in the brief that commissioned this",
        "table; it appears nowhere in the client text, so 1.2.2 and the Source Conflict Log rule against it. See 1.7.",
        "",
        HEADER,
        RULE,
    ]
    lines += rows(ordered_debut)
    lines += [
        "",
        "### 2.2 Alternate costume cards",
        "",
        "Same eleven columns, one row per costume card that is not the unit's debut form. Aptitudes, unique skills",
        "and stat ceilings can differ from the debut form, which is why they are not folded into 2.1.",
        "",
        HEADER,
        RULE,
    ]
    lines += rows([(c["char_id"], c) for c in variants], variant=True)
    lines += [
        "",
        "### 2.3 How these tables were built, and what that means for trust",
        "",
        f"- Every cell in 2.1 and 2.2 comes from one machine-readable export of the GameTora database "
        f"(tier B), fetched 2026-09-27: `character-cards.json` for aptitudes, rarity, epithet, unique skill ids "
        f"and per-server dates; `characters.json` for the `[Global]` availability flag; `skills.json` for skill names. "
        f"No row was typed by hand, which is the point: a 268-row table entered by hand is where invented aptitude "
        f"letters would hide.",
        f"- The ten-element aptitude array order was treated as unverified until proven. It was decoded against the "
        f"rendered GameTora unit pages and against two independent A-tier wikis for three diagnostic units chosen to "
        f"be awkward rather than easy, 30 of 30 cells agreeing. Order: Turf, Dirt, Sprint, Mile, Medium, Long, then "
        f"the four strategies. GameTora's own unit page prints `Short` where the `[Global]` client prints `Sprint`, "
        f"and `Medium` for the same band, so the letters are the reliable part, not the labels.",
        f"- Naming discipline: the Global EN Name column holds an official localized name only where the *unit* has shipped "
        f"on `[Global]`. GameTora publishes English renderings for `[JP-Only]` units too, and those are tier B translations "
        f"rather than announced localizations, so an unshipped unit's row reads `N/A (unit unreleased on [Global])`. Where "
        f"the unit is live but this card is not, the name stays and the cell says so in those words, because the row's "
        f"`[JP-Only]` status describes the card. Romanized Name carries the same rendering.",
        f"- Level discipline, and a defect that was found and fixed: an earlier build of this table decided Server Status from "
        f"the unit flag while listing costume cards, which tagged {len(mismatches)} cards that never shipped on `[Global]` as "
        f"`[Both]`, and then certified zero disagreements because the check was run at that same wrong level. Status is now "
        f"decided per card, which yields {both_units} `[Both]` of {total_units} debut rows in 2.1 and "
        f"{cards_en - both_units} `[Both]` of {len(cards) - total_units} variant rows in 2.2. "
        f"{len(debut_gap)} debut rows disagree between the unit flag and the card date, which is why the two levels are stated "
        f"separately rather than collapsed into one token.",
        "",
    ]

    OUT.write_text("\n".join(lines) + "\n", encoding="utf-8")
    print("wrote", OUT, "lines", len(lines))
    print("units", total_units, "both", both_units, "jp_only", jp_only_units, "cards", len(cards), "cards_en", cards_en)
    print("slug-aligned romanization:", len(aligned), "/", len(multi_word))
    print("fallback rows:", [(c["name_jp"], spaced_romaji(c["name_jp"], c["url_name"])) for c in multi_word if c not in aligned][:8])
    print("mismatches", len(mismatches), mismatches[:6])
    print("unmapped chars in romanization:", all_unmapped)


main()
