#!/usr/bin/env python3
"""Derive docs/adr/README.md's index table from the ADR files themselves.

Replaces the hand-maintained table and the `head -1`/`awk '/^Status:/'` snippet that
currently produce a snapshot which is wrong the moment a file lands. Reads the
ADR-CONTRACT block (see docs/adr/ADR-TEMPLATE.md) and emits one row per ADR.

Usage:
    python3 tools/adr-index.py docs/adr            # print the table
    python3 tools/adr-index.py docs/adr --write    # splice it into docs/adr/README.md
"""
import argparse
import pathlib
import re
import sys

FIELDS = ("decision", "code", "superseded-by", "amended-by", "open")
ADR_FILE = re.compile(r"^(00\d\d)-(.+)\.md$")
BLOCK = re.compile(r"<!--\s*ADR-CONTRACT\s*(.*?)\s*-->", re.S)
KV = re.compile(r"^\s*(decision|code|superseded-by|amended-by|open)\s*:\s*(.*?)\s*$", re.M)
HEADING = re.compile(r"^#\s*ADR-\d{4}:\s*(.+?)\s*$", re.M)


def parse(path: pathlib.Path) -> dict | None:
    m = ADR_FILE.match(path.name)
    if not m:
        return None
    num, slug = m.group(1), m.group(2)
    text = path.read_text(encoding="utf-8")
    heading = HEADING.search(text)
    block = BLOCK.search(text)
    fields = dict(KV.findall(block.group(1))) if block else {}
    return {
        "num": num,
        "slug": slug,
        "subject": (heading.group(1) if heading else slug.replace("-", " ")).strip(),
        "has_contract": bool(block),
        **{f: fields.get(f, "") for f in FIELDS},
    }


def load(adr_dir: pathlib.Path) -> list[dict]:
    return sorted(
        (r for r in (parse(p) for p in adr_dir.glob("*.md")) if r),
        key=lambda r: r["num"],
    )


def table(rows: list[dict]) -> str:
    head = ("| ADR | Subject | Decision | Code | Superseded / amended by | Still open |",
            "|---|---|---|---|---|---|")
    body = []
    for r in rows:
        parts = [x.strip() for x in (r["superseded-by"], r["amended-by"]) if x.strip() and x.strip() != "—"]
        refs = " · ".join(f"`ADR-{p[:4]}` {p[4:].strip()}".rstrip() for p in parts) or "—"
        cells = [r["num"], r["subject"], r["decision"] or "MISSING",
                 r["code"] or "MISSING", refs, r["open"] or "—"]
        body.append("| " + " | ".join(c.replace("|", "\\|") for c in cells) + " |")
    return "\n".join((*head, *body))


MARK_BEGIN = "<!-- adr-index:begin -->"
MARK_END = "<!-- adr-index:end -->"


def splice(readme: pathlib.Path, rendered: str) -> bool:
    text = readme.read_text(encoding="utf-8")
    if MARK_BEGIN not in text or MARK_END not in text:
        print(f"{readme}: markers {MARK_BEGIN} / {MARK_END} not found; not modified", file=sys.stderr)
        return False
    pre, rest = text.split(MARK_BEGIN, 1)
    _, post = rest.split(MARK_END, 1)
    new = f"{pre}{MARK_BEGIN}\n\n{rendered}\n\n{MARK_END}{post}"
    if new == text:
        return True
    readme.write_text(new, encoding="utf-8")
    return True


def main() -> int:
    ap = argparse.ArgumentParser()
    ap.add_argument("adr_dir", nargs="?", default="docs/adr", type=pathlib.Path)
    ap.add_argument("--write", action="store_true")
    a = ap.parse_args()
    rows = load(a.adr_dir)
    if not rows:
        print(f"no ADR files matched in {a.adr_dir}", file=sys.stderr)
        return 2
    rendered = table(rows)
    if a.write:
        if not splice(a.adr_dir / "README.md", rendered):
            return 1
        print(f"{a.adr_dir / 'README.md'}: index regenerated from {len(rows)} ADR files")
    else:
        print(rendered)
    missing = [r["num"] for r in rows if not r["has_contract"]]
    if missing:
        print(f"\nno ADR-CONTRACT block: {', '.join(missing)}", file=sys.stderr)
    return 0


if __name__ == "__main__":
    sys.exit(main())
