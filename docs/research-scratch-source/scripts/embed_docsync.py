"""Embed source markdown files verbatim into the research-scratch masters that own their subject.

Convention (same as the PLAN-DOC-SYNC embed this tool came from): a `## <basename.md>` section, a
banner naming the capture sha and counts, then the body verbatim with the first H1 consumed as a plain
line and every later heading demoted one level. Fenced code blocks are left alone.

Fidelity is asserted before anything is written: every non-blank non-heading source line must appear
verbatim in the appended region, and every heading except a consumed leading H1 must appear demoted by
exactly one level. Run: python research-scratch/scripts/embed_docsync.py [--write]
"""

import io
import re
import subprocess
import sys

PAIRS = [
    ("research-scratch/trainer-advisor.md", "docs/research-scratch/PLANS-AND-BRIEFS.md"),
]
CAPTURE = "2026-10-04"
BRANCH_NOTE = (
    "It was read from the tip of the branch trainer-desk-2.0 (`19c2e7d`) and never existed on master, "
    "so no working file is deleted here. Its authority chain is branch-only: it cites ADR-0020 three "
    "times plus PRD US-13, US-14 and FR-F, none of which are on master. Read its own status line as "
    "written: spec, awaiting owner review, no code."
)
HEAD = re.compile(r"^(#{1,6}) (.*)$")
FENCE = re.compile(r"^\s*```")

sha = subprocess.run(["git", "rev-parse", "--short", "HEAD"], capture_output=True, text=True).stdout.strip()
failed = []
accum = {}

for src, master in PAIRS:
    src_lines = io.open(src, encoding="utf-8").read().split("\n")
    # A leading H1 is consumed as a plain line; a body with no H1 (calendar-tables.md is pure tables)
    # loses nothing. Only level-1 headings in the first three lines qualify.
    lead = next((i for i, l in enumerate(src_lines[:3]) if l.startswith("# ")), None)

    out, in_code, consumed = [], False, False
    demoted = []
    for i, line in enumerate(src_lines):
        if FENCE.match(line):
            in_code = not in_code
            out.append(line)
            continue
        m = HEAD.match(line) if not in_code else None
        if m and not consumed and i == lead:
            out.append(m.group(2).strip())
            consumed = True
            continue
        if m:
            demoted.append(line)
            out.append("#" + line)
        else:
            out.append(line)

    name = src.split("/")[-1]
    nb = len([l for l in src_lines if l.strip()])
    banner = (f"> Captured {CAPTURE} at `{sha}`: {len(src_lines) - 1} lines, {nb} non-blank, "
              f"embedded verbatim with headings demoted one level. {BRANCH_NOTE}")
    section = f"## {name}\n\n{banner}\n\n" + "\n".join(out)

    # Two sources can share a master, so each append builds on the accumulated text, not the disk copy.
    base = accum.get(master)
    if base is None:
        base = io.open(master, encoding="utf-8").read()
    master_lines = base.split("\n")
    while master_lines and master_lines[-1] == "":
        master_lines.pop()
    merged = "\n".join(master_lines) + "\n\n---\n\n" + section.rstrip() + "\n"
    accum[master] = merged

    region = set(merged.split(f"## {name}\n", 1)[1].split("\n"))
    miss_text = [l for l in src_lines if l.strip() and not HEAD.match(l) and l not in region]
    miss_head = [l for l in demoted if "#" + l not in region]
    print(f"{name:38} -> {master.split('/')[-1]:28} lines={len(src_lines)-1:5} "
          f"nonblank={nb:5} demoted={len(demoted):3} unmatched_text={len(miss_text)} "
          f"unmatched_heads={len(miss_head)}")
    for l in (miss_text + miss_head)[:4]:
        print("   MISS " + repr(l[:120]))
    if miss_text or miss_head:
        failed.append(name)

if failed:
    sys.exit("nothing written, fidelity failures: " + ", ".join(failed))
if "--write" not in sys.argv:
    print("dry run: all pairs verified, pass --write to apply")
    sys.exit(0)

for master, merged in accum.items():
    io.open(master, "w", encoding="utf-8", newline="\n").write(merged)
    print(f"wrote {master} ({len(merged.splitlines())} lines)")
