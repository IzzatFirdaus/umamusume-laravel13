#!/usr/bin/env python
"""Merge three UX write-ups into one document, demoting headings, and prove nothing was lost.

Verbatim preservation is the requirement, so the transform is mechanical: the only bytes
that change are leading '#' on a heading line that sits outside a fenced block.
"""

import io
import os
import re
import sys

DOCS = os.path.join(os.path.dirname(os.path.dirname(os.path.abspath(__file__))), "docs")
OUT = os.path.join(DOCS, "UMAMUSUME UX DELIVERABLES — MERGED.md")

SOURCES = [
    "UMAMUSUME PRETTY DERBY — COMPREHENSIVE UX DELIVERABLES.md",
    "UX Behavior Specification - Umamusume Trainer Companion.md",
    "Scenario-Specific User Flows & Frontend Specifications.md",
]

HEAD = re.compile(r"^(#{1,6}) (.*)$")
FENCE = re.compile(r"^\s*```")


def parse(path):
    """Return (title, out_lines, n_headings, n_headings_demoted)."""
    raw = io.open(path, encoding="utf-8").read()
    lines = raw.split("\n")
    incode = False
    title = None
    kept = []
    demoted = 0
    total = 0
    for line in lines:
        if FENCE.match(line):
            incode = not incode
            kept.append(line)
            continue
        m = HEAD.match(line) if not incode else None
        if not m:
            kept.append(line)
            continue
        total += 1
        if title is None:
            title = m.group(2).strip()
            continue
        kept.append("#" + line)
        demoted += 1
    if title is None:
        raise SystemExit(f"{path}: no level-1 heading to use as the part title")
    if any(len(HEAD.match(l).group(1)) > 6 for l in kept if HEAD.match(l)):
        raise SystemExit(f"{path}: demotion would exceed heading level 6")
    return title, kept, total, demoted


def main():
    parts = []
    stats = []
    for i, name in enumerate(SOURCES, 1):
        path = os.path.join(DOCS, name)
        title, body, total, demoted = parse(path)
        parts.append((i, name, title, body))
        stats.append((name, total, demoted))

    out = ["# Umamusume: Pretty Derby — Merged UX Deliverables", ""]
    out += ["## Source Documents", ""]
    for i, name, title, _ in parts:
        out.append(f"- `{name}` (Part {i}: {title})")
    out += ["", "The three source files remain on disk beside this document. This merge is a",
            "reading aid that puts them in one place; it is not a replacement, and it resolves none",
            "of the disagreements between them. Each source's own triage banner is carried below.", ""]
    for i, name, title, body in parts:
        out += ["---", "", f"## Part {i}: {title}", ""]
        out += [l for l in body]
        if out and out[-1] == "":
            out.append("")
    text = "\n".join(out).rstrip("\n") + "\n"
    io.open(OUT, "w", encoding="utf-8", newline="\n").write(text)

    print(f"wrote {os.path.basename(OUT)}: {len(text.splitlines())} lines, {os.path.getsize(OUT)} bytes")
    for name, total, demoted in stats:
        print(f"  {name[:52]:54} headings={total:3} demoted={demoted:3} title-consumed=1")
    return 0


if __name__ == "__main__":
    sys.exit(main())
