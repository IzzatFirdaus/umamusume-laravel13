"""Stitch the section drafts into the reference document body.

Ordering is explicit rather than file-order, because the drafts were written by
different researchers and 1.1 and 1.3 share a file.
"""

import re
from pathlib import Path

S = Path("D:/Projects/umamusume-laravel13/research-scratch")
BODY = S / "assembled-body.md"
METRICS = S / "metrics.txt"


def lines(name):
    return (S / name).read_text(encoding="utf-8").splitlines()


def find(ls, pat, start=0):
    rx = re.compile(pat)
    for i in range(start, len(ls)):
        if rx.match(ls[i]):
            return i
    raise SystemExit("heading not found: %r (searched %s)" % (pat, name_of(pat)))


name_of = lambda pat: pat  # noqa: E731


def slice_between(ls, start_pat, end_pats):
    """Return [start, end) where end is the first line matching any end pattern."""
    a = find(ls, start_pat)
    b = len(ls)
    for ep in end_pats:
        rx = re.compile(ep)
        for i in range(a + 1, len(ls)):
            if rx.match(ls[i]):
                b = min(b, i)
                break
    return ls[a:b]


def strip_conflict_log(block):
    """Cut a draft's own Source Conflict Log out; rows go to the consolidated log."""
    out, i = [], 0
    while i < len(block):
        if re.match(r"^#+ Source Conflict Log", block[i]):
            i += 1
            while i < len(block) and not block[i].startswith("#"):
                i += 1
            continue
        out.append(block[i])
        i += 1
    while out and out[-1].strip() == "":
        out.pop()
    return out


def conflict_rows(block, origin):
    rows = []
    grab = False
    for ln in block:
        if re.match(r"^#+ Source Conflict Log", ln):
            grab = True
            continue
        if grab:
            if ln.startswith("#"):
                grab = False
                continue
            if ln.startswith("|"):
                cells = [c.strip() for c in ln.strip().strip("|").split("|")]
                if len(cells) < 6:
                    continue
                if cells[0].strip("-# ") == "" or cells[0].lower() in {"#", "data point", "---"}:
                    continue
                if set("".join(cells)) <= set("-: "):
                    continue
                rows.append(cells)
    return [r for r in rows if r[0] != "#"] or rows


def demote(block, by=1):
    return ["#" * by + ln if ln.startswith("#") else ln for ln in block]


def clean(block):
    out = [ln.rstrip() for ln in block]
    while out and out[0].strip() == "":
        out.pop(0)
    while out and out[-1].strip() == "":
        out.pop()
    return out


def main():
    tr = lines("draft-sec1-training-stats.md")
    ra = lines("draft-sec1-race.md")
    su = lines("draft-sec1-support-inheritance.md")
    mo = lines("draft-sec1-modes.md")
    li = lines("draft-sec3-4-liveops.md")
    s2 = lines("gen-sec2.md")
    cc = lines("sec2-crosscheck.md")

    tr_log = slice_between(tr, r"^##+ Source Conflict Log", [r"^(?!#)"])
    parts = {
        "1.1": slice_between(tr, r"^## 1\.1 ", [r"^## 1\.3 ", r"^## Source Conflict Log"]),
        "1.2": ["## 1.2 Race Mechanics", ""]
        + slice_between(ra, r"^## 1\.2\.1", [r"^## Source Conflict Log"]),
        "1.3": slice_between(tr, r"^## 1\.3 ", [r"^## Source Conflict Log"]),
        "1.4": slice_between(su, r"^## 1\.4 ", [r"^## 1\.5 "]),
        "1.5": slice_between(su, r"^## 1\.5 ", [r"^## Source Conflict Log"]),
        "1.6": ["## 1.6 Additional Systems and Game Modes", ""]
        + slice_between(mo, r"^## 1\.6\.0", [r"^## 1\.7 "]),
        "1.7": slice_between(mo, r"^## 1\.7 ", [r"^## Source Conflict Log"]),
        "3": slice_between(li, r"^## 3\.1 ", [r"^# 4 "]),
        "4": slice_between(li, r"^## 4\.1 ", [r"^## Source Conflict Log"]),
    }

    logs = {
        "1.1/1.3": conflict_rows(tr, "1.1/1.3"),
        "1.2": conflict_rows(ra, "1.2"),
        "1.4/1.5": conflict_rows(su, "1.4/1.5"),
        "1.6/1.7": conflict_rows(mo, "1.6/1.7"),
        "3/4": conflict_rows(li, "3/4"),
    }

    doc = ["## Section 1 — Core Game Mechanics", ""]
    for key in ["1.1", "1.2", "1.3", "1.4", "1.5", "1.6", "1.7"]:
        doc += demote(clean(strip_conflict_log(parts[key]))) + ["", ""]
    doc = doc[: len(doc) - 1]

    doc += ["", "## Section 2 — Character Roster", ""]
    doc += clean(s2)[2:]  # drop the generated file's own H2 and its intro line
    doc += ["", "### 2.4 Independent cross-check of the roster data", ""]
    doc += demote(clean(cc)[1:], by=2)

    doc += ["", "## Section 3 — Live Operations & Events", ""]
    doc += demote(clean(strip_conflict_log(parts["3"])))
    doc += ["", "## Section 4 — Gacha & Banner Schedule", ""]
    doc += demote(clean(strip_conflict_log(parts["4"])))

    doc += [
        "",
        "## Source Conflict Log",
        "",
        "Consolidated from the five research drafts. One row per disagreement between sources that survived review.",
        "",
        "| # | From | Data Point | Source A | Source B | Conflict | Resolution | Confidence |",
        "|---|---|---|---|---|---|---|---|",
    ]
    n = 0
    for origin, rows in logs.items():
        for r in rows:
            n += 1
            # each draft row is its own number plus six columns; renumber here
            padded = (r[1:] + ["", "", "", "", "", ""])[:6]
            doc.append("| %d | %s | %s |" % (n, origin, " | ".join(padded)))
    doc.append("")

    BODY.write_text("\n".join(doc) + "\n", encoding="utf-8")

    m = []
    m.append("conflict rows consolidated: %d" % n)
    for origin, rows in logs.items():
        m.append("  %-8s rows=%d  cols_per_row=%s" % (origin, len(rows), sorted({len(r) for r in rows})))
    text = BODY.read_text(encoding="utf-8")
    m.append("body lines: %d" % len(doc))
    m.append("body chars: %d" % len(text))
    for token in ["⚠️ STALE", "❌ UNVERIFIED", "[RUMOR]", "[DATAMINE]", "[SPECULATION]"]:
        m.append("%-14s %d" % (token, text.count(token)))
    m.append("markdown links: %d" % len(re.findall(r"\]\(https?://", text)))
    m.append("distinct link targets: %d" % len(set(re.findall(r"\]\((https?://[^)\s]+)", text))))
    for tok in ["[JP]", "[Global]", "[Both]", "[JP-Only]"]:
        m.append("%-10s %d" % (tok, text.count(tok)))
    m.append("empty table cells (| |): %d" % text.count("| | |"))
    METRICS.write_text("\n".join(m) + "\n", encoding="utf-8")
    print("\n".join(m))


main()
