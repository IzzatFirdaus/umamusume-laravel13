#!/usr/bin/env python3
"""Guard the ADR set against the failure modes that made docs/adr expensive to read.

Each check names the files it is talking about, so the output is actionable rather than
advisory. Run it in CI next to the doc tests.

    python3 tools/adr-check.py docs/adr
"""
import argparse
import pathlib
import re
import sys

FIELDS = ("decision", "code", "superseded-by", "amended-by", "open")

sys.path.insert(0, str(pathlib.Path(__file__).resolve().parent))
from adr_rows import numeric_rows

ADR_FILE = re.compile(r"^(00\d\d)-(.+)\.md$")
CONTRACT = re.compile(r"<!--\s*ADR-CONTRACT\s*(.*?)\s*-->", re.S)
KV = re.compile(r"^\s*(decision|code|superseded-by|amended-by|open)\s*:\s*(.*?)\s*$", re.M)
MAX_PREAMBLE_LINES = 24          # status/contract block above the first `## `
MAX_DECISION_WORDS = 500          # the immutable part, excluding the ledger


def decision_section(text: str) -> str:
    m = re.search(r"^## Decision\s*\n(.*?)(?=^## )", text, re.S | re.M)
    if not m:
        m = re.search(r"^## Decision\s*\n(.*)$", text, re.S | re.M)
    return m.group(1) if m else ""


def load(adr_dir: pathlib.Path) -> dict[str, dict]:
    out = {}
    for p in sorted(adr_dir.glob("*.md")):
        m = ADR_FILE.match(p.name)
        if not m:
            continue
        text = p.read_text(encoding="utf-8")
        c = CONTRACT.search(text)
        out[m.group(1)] = {
            "path": p, "text": text, "slug": m.group(2),
            "preamble": text.split("\n## ", 1)[0],
            "decision_section": decision_section(text),
            "contract": dict(KV.findall(c.group(1))) if c else None,
        }
    return out


def check(adr_dir: pathlib.Path) -> list[tuple[str, str, str]]:
    docs = load(adr_dir)
    rows: dict[str, dict[tuple, set[str]]] = {}
    rel = re.compile(r"ADR-(00\d\d)")
    fails: list[tuple[str, str, str]] = []
    if not docs:
        return [("FATAL", str(adr_dir), "no ADR files matched")]

    # 1. every ADR on disk is indexed, and every index row names a file that exists
    readme = adr_dir / "README.md"
    indexed = set()
    if readme.exists():
        indexed = set(re.findall(r"^\|\s*(00\d\d)\s*\|", readme.read_text(encoding="utf-8"), re.M))
        for n in sorted(set(docs) - indexed):
            fails.append(("INDEX", n, "ADR-00%s is on disk but has no index row" % n[-2:]))
        for n in sorted(indexed - set(docs)):
            fails.append(("INDEX", n, "index row for ADR-00%s, which is not on disk" % n[-2:]))
    else:
        fails.append(("INDEX", "README", "no README.md to index against"))

    # 2. an authority relation must be visible from the target's own preamble.
    #    Soft pointers (Relates to / Related / See / precede|follow) are citations, not
    #    claims about the target's standing, so they do not have to be acknowledged back.
    HARD = r"(supersed\w*|amend\w*|withdraw\w*|widen\w*|narrow\w*|extend\w*|absorb\w*|lift\w*|reopen\w*|reaffirm\w*)"
    SOFT = r"(relat\w*|see also|\bsee\b|preced\w*|follow\w*|pointe?\w*\s+to)"
    for src, d in docs.items():
        for m in rel.finditer(d["text"]):
            tgt = m.group(1)
            if tgt == src or tgt not in docs:
                continue
            window = d["text"][max(0, m.start() - 90): m.start()].replace("\n", " ")
            if re.search(SOFT + r"[^.;]{0,40}$", window, re.I):
                continue
            if not re.search(HARD, window, re.I):
                continue
            if re.search(r"\b" + src + r"\b", docs[tgt]["preamble"]):
                continue
            fails.append(("ASYM", src,
                          "claims a relation over ADR-00%s (`%s`) that ADR-00%s's preamble does not "
                          "acknowledge" % (tgt[-2:], re.search(HARD, window, re.I).group(1).lower(), tgt[-2:])))

    # 3. orphans: a binding decision nothing else cites is a decision nobody finds
    cited = set()
    for d in docs.values():
        cited |= set(rel.findall(d["text"]))
    for src, d in docs.items():
        if src not in cited and src not in indexed:
            fails.append(("ORPHAN", src, "cited by no other ADR and absent from the index"))
        elif src not in cited:
            fails.append(("ORPHAN", src, "indexed but cited by no other ADR"))

    # 4. preamble bloat: the header is what every session reads
    for src, d in docs.items():
        n = len(d["preamble"].rstrip().split("\n"))
        if n > MAX_PREAMBLE_LINES:
            fails.append(("PREAMBLE", src,
                          "preamble is %d lines (cap %d); corrections belong in the ledger" %
                          (n, MAX_PREAMBLE_LINES)))
        blocks = re.findall(r"<!--\s*ADR-CONTRACT\s*(.*?)\s*-->", d["text"], re.S)
        if len(blocks) > 1:
            fails.append(("CONTRACT", src,
                          "%d ADR-CONTRACT blocks; only the first is read, so a second one is invisible "
                          "to the index" % len(blocks)))
        stray = [k for k in re.findall(r"^\s*([a-z][a-z-]{2,})\s*:", blocks[0] if blocks else "", re.M)
                 if blocks and k not in FIELDS]
        if stray:
            fails.append(("CONTRACT", src,
                          "unrecognised key(s) in the block: %s — legend text does not belong inside "
                          "ADR-CONTRACT" % ", ".join(sorted(set(stry := set(stry))))))
        if d["contract"] is None:
            fails.append(("CONTRACT", src, "no ADR-CONTRACT block; the index cannot be derived"))
        else:
            for f in ("decision", "code", "open"):
                if not d["contract"].get(f):
                    fails.append(("CONTRACT", src, "ADR-CONTRACT field `%s` is empty" % f))
        w = len(d["decision_section"].split())
        if w > MAX_DECISION_WORDS:
            fails.append(("DECISION", src, "Decision section is %d words (cap %d)" % (w, MAX_DECISION_WORDS)))

    # 5. data rows typed into more than one ADR. Two shapes matter:
    #    (a) the same label carrying different numbers in different ADRs — a copy has drifted;
    #    (b) the same label and numbers in two ADRs — one of them is redundant.
    for src, d in docs.items():
        for label, vals in numeric_rows(d["text"]):
            rows.setdefault(label, {}).setdefault(vals, set()).add(src)
    for label, variants in sorted(rows.items()):
        per_file: dict[str, set[tuple]] = {}
        for vals, srcs in variants.items():
            for s in srcs:
                per_file.setdefault(s, set()).add(vals)
        for a_file, a_rows in sorted(per_file.items()):
            for b_file, b_rows in sorted(per_file.items()):
                if a_file >= b_file:
                    continue
                for av in sorted(a_rows):
                    for bv in sorted(b_rows):
                        n = min(len(av), len(bv))
                        if n < 3 or av == bv:
                            continue
                        if av[:n] == bv[:n]:
                            fails.append(("DUPE", a_file,
                                          "`%s` is typed in ADR-00%s and again in ADR-00%s with an extra "
                                          "column (%s vs %s). A prefix copy is still a copy: it can drift "
                                          "and no test spans the two" %
                                          (label, a_file[-2:], b_file[-2:], " / ".join(av), " / ".join(bv))))
                        elif av[:3] == bv[:3]:
                            fails.append(("DRIFT", a_file,
                                          "`%s` disagrees between ADR-00%s (%s) and ADR-00%s (%s) from "
                                          "column 4 on" %
                                          (label, a_file[-2:], " / ".join(av), b_file[-2:], " / ".join(bv))))
        for vals, srcs in variants.items():
            if len(srcs) > 1:
                fails.append(("DUPE", sorted(srcs)[0],
                              "row `%s` = %s is typed identically in %s; the config or table it mirrors "
                              "should be its only owner" %
                              (label, " / ".join(vals), " and ".join("ADR-" + s for s in sorted(srcs)))))

    # 6. a table interrupted mid-body. Everything after the interruption stops parsing as
    #    table rows, so a row placed there is invisible to a reader scanning the columns.
    #    An interlude counts only if table rows resume after it and the resumed run keeps
    #    the interrupted table's column count (a fresh table has its own separator row).
    def cols(line):
        return line.count("|")

    for src, d in docs.items():
        lines = d["text"].split("\n")
        i = 0
        while i < len(lines) - 2:
            if not (lines[i].lstrip().startswith("|") and lines[i + 1].lstrip().startswith("|")):
                i += 1
                continue
            j = i
            while j < len(lines) and lines[j].lstrip().startswith("|"):
                j += 1
            width = cols(lines[i])
            k = j
            while k < len(lines) and not lines[k].strip():
                k += 1
            m = k
            while m < len(lines) and not lines[m].lstrip().startswith("|"):
                m += 1
            heading_between = any(l.lstrip().startswith("#") for l in lines[k:m])
            e = m
            while e < len(lines) and lines[e].lstrip().startswith("|"):
                e += 1
            # a 1- or 2-row run followed by its own separator row is a NEW table, not a
            # continuation of the interrupted one
            is_new_table = e - m <= 2 and any("---" in x for x in lines[m:e])
            if (m - k >= 2 and m < len(lines) and cols(lines[m]) == width
                    and not heading_between and not is_new_table):
                fails.append(("MARKDOWN", src,
                              "lines %d-%d interrupt a table; the rows after them no longer parse as part "
                              "of it — move the note below the table or into a column" %
                              (k + 1, m)))
                i = m
            else:
                i = j
    # 7. dates in the corpus older than the newest one, used as if current
    return fails


def main() -> int:
    ap = argparse.ArgumentParser()
    ap.add_argument("adr_dir", nargs="?", default="docs/adr", type=pathlib.Path)
    a = ap.parse_args()
    fails = check(a.adr_dir)
    order = ("FATAL", "INDEX", "ASYM", "DRIFT", "DUPE", "MARKDOWN", "CONTRACT", "PREAMBLE", "DECISION", "ORPHAN")
    by = {}
    for code, src, msg in fails:
        by.setdefault(code, []).append((src, msg))
    for code in order:
        for src, msg in sorted(by.get(code, [])):
            print(f"{code:<9}{src:<7}{msg}")
    print(f"\n{len(fails)} finding(s) in {a.adr_dir}")
    hard = [c for c, _, _ in fails if c in ("FATAL", "INDEX", "CONTRACT", "MARKDOWN", "DUPE", "DRIFT")]
    return 1 if hard else 0


if __name__ == "__main__":
    sys.exit(main())
