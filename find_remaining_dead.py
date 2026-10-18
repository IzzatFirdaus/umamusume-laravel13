#!/usr/bin/env python
"""Find remaining dead links for top deleted targets, show context."""

import subprocess, os, re

REPO = os.path.dirname(os.path.abspath(__file__))

def git(*args):
    return subprocess.run(["git", "-c", "core.quotepath=false", *args], cwd=REPO,
                          capture_output=True, text=True, encoding="utf-8", errors="replace").stdout

def md_files():
    return [p for p in git("ls-files", "--", "*.md").splitlines() if p.strip()]

TARGETS = {'RAW-FINDINGS.md', 'SKILLS-GAPS.md', 'SOURCE-OF-TRUTH.md',
           'docs/proposals/provenance-and-absence-contracts.md', 'calendar-tables.md',
           'docs/design-research/SKILLS-GAPS.md'}

dead = []
listing = set(git("ls-files").splitlines())
tracked_md_names = {os.path.basename(p) for p in listing if p.endswith(".md")}
for f in md_files():
    near = os.path.dirname(f)
    try:
        text = open(os.path.join(REPO, f), encoding="utf-8", errors="replace").read()
    except OSError:
        continue
    for n, line in enumerate(text.split("\n"), 1):
        for ref in re.findall(r"`((?:[\w./&-]+/)?[\w.-]+\.md)`", line):
            cand = ref.replace("\\", "/").lstrip("/")
            if cand in TARGETS:
                dead.append((f, n, ref, line.strip()))

print(f'Found {len(dead)} citations to top deleted targets:')
for f, n, ref, line in dead:
    print(f'  {f}:{n} -> `{ref}`')
    print(f'    Context: {line[:120]}')
