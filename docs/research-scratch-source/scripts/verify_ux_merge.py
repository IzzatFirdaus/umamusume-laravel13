#!/usr/bin/env python
"""Independent check of the merge: rebuild what each part should contain and diff it against
what the merged file actually holds. Does not reuse merge_ux_docs.py's logic on purpose."""

import io
import os
import re

DOCS = os.path.join(os.path.dirname(os.path.dirname(os.path.abspath(__file__))), "docs")
OUT = os.path.join(DOCS, "UMAMUSUME UX DELIVERABLES — MERGED.md")
SOURCES = [
    "UMAMUSUME PRETTY DERBY — COMPREHENSIVE UX DELIVERABLES.md",
    "UX Behavior Specification - Umamusume Trainer Companion.md",
    "Scenario-Specific User Flows & Frontend Specifications.md",
]
HEAD = re.compile(r"^(#{1,6}) (.*)$")
FENCE = re.compile(r"^\s*```")


def expected(name):
    lines = io.open(os.path.join(DOCS, name), encoding="utf-8").read().split("\n")
    incode, seen_title, out = False, False, []
    for line in lines:
        if FENCE.match(line):
            incode = not incode
            out.append(line)
            continue
        m = HEAD.match(line) if not incode else None
        if m and not seen_title:
            seen_title = True
            continue
        out.append("#" + line if m else line)
    while out and out[-1] == "":
        out.pop()
    while out and out[0] == "":
        out.pop(0)
    return out, seen_title


merged = io.open(OUT, encoding="utf-8").read().split("\n")
marks = [i for i, l in enumerate(merged) if re.match(r"^## Part \d+: ", l)]
print(f"part markers found: {len(marks)} at lines {marks}")
ok = True
for idx, (name, start) in enumerate(zip(SOURCES, marks), 1):
    stop = marks[idx] if idx < len(marks) else len(merged)
    body = [l for l in merged[start + 1:stop]]
    while body and body[0] == "":
        body.pop(0)
    while body and body[-1] in ("", "---"):
        body.pop()
    exp, saw_title = expected(name)
    same = exp == body
    print(f"Part {idx}: expected {len(exp)} lines, merged body {len(body)} lines, identical={same}")
    if not same:
        ok = False
        for n, (a, b) in enumerate(zip(exp, body)):
            if a != b:
                print(f"   first diff at line {n}: expected={a[:90]!r} actual={b[:90]!r}")
                break
        if len(exp) != len(body):
            print(f"   length mismatch: {len(exp)} vs {len(body)}")
    else:
        print(f"   title consumed: {saw_title}, no bytes altered outside heading '#'")

# structural checks
h1 = [l for l in merged if re.match(r"^# ", l)]
depths = {}
incode = False
for l in merged:
    if FENCE.match(l):
        incode = not incode
        continue
    m = HEAD.match(l) if not incode else None
    if m:
        depths[len(m.group(1))] = depths.get(len(m.group(1)), 0) + 1
print(f"level-1 headings in merged: {len(h1)} (must be 1: the merged title)")
print(f"heading depth distribution: {dict(sorted(depths.items()))} total={sum(depths.values())}")
bad = [l for l in merged if re.match(r"^#{7,}", l)]
print(f"headings deeper than 6: {len(bad)}")
banners = [l for l in merged if "NOT MERGED" in l or "not merged" in l][:6]
print(f"triage banner lines carried: {len([l for l in merged if l.startswith('> ')])} blockquote lines")
for b in banners:
    print(f"   {b[:96]}")
print("VERIFICATION", "PASS" if ok and len(h1) == 1 and not bad else "FAIL")
