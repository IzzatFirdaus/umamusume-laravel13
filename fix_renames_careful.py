#!/usr/bin/env python
"""Carefully fix renamed-path dead links, excluding worktree/hidden paths."""

import subprocess, os, re, json

REPO = os.path.dirname(os.path.abspath(__file__))

def git(*args):
    return subprocess.run(["git", "-c", "core.quotepath=false", *args], cwd=REPO,
                          capture_output=True, text=True, encoding="utf-8", errors="replace").stdout

def md_files():
    return [p for p in git("ls-files", "--", "*.md").splitlines() if p.strip()]

def dead_links():
    bad = []
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
                tries = [cand]
                if "/" not in cand:
                    tries += [f"docs/{cand}", f"docs/research-scratch/{cand}",
                              f"{near}/{cand}" if near else cand]
                else:
                    tries += [f"docs/{cand}", f"{near}/{cand}" if near else cand]
                if any(t in listing for t in tries):
                    continue
                if "/" not in cand and cand in tracked_md_names:
                    continue
                target = next((t for t in tries if os.path.exists(os.path.join(REPO, t))), None)
                bad.append((f, n, ref, "UNTRACKED" if target else "GONE"))
    return bad

# Get all existing md files in the repo (NOT worktrees)
existing_md = set()
listing = git("ls-files").splitlines()
for p in listing:
    if p.endswith('.md'):
        existing_md.add(p.replace('\\', '/'))

dead = dead_links()

stub_pointers = {'CONSTRAINTS.md', 'PLAN.md'}
EXCLUDED_PREFIXES = ('.kilo/worktrees/', '.copilot/', '.qoder/', '.ai/',
                     'test-results/', '.github/')

# Find renames: deleted items whose basename matches an existing repo file
renames = []
for f, n, ref, kind in dead:
    if ref in stub_pointers:
        continue
    target_filename = os.path.basename(ref)
    matches = [p for p in existing_md
               if os.path.basename(p) == target_filename
               and p != ref
               and not p.startswith(EXCLUDED_PREFIXES)]
    if matches:
        renames.append((f, n, ref, matches[0]))

print(f'Valid rename candidates: {len(renames)}')
from collections import Counter
target_counts = Counter(t for _, _, _, t in renames)
for target, count in target_counts.most_common(20):
    print(f'  {count}x -> {target}')

# Group by file and apply
by_file = {}
for f, n, ref, new_ref in renames:
    if f not in by_file:
        by_file[f] = []
    by_file[f].append((ref, new_ref))

print(f'\nFiles to fix: {len(by_file)}')
total = sum(len(v) for v in by_file.values())
print(f'Total repoints: {total}')

for f, fixes in by_file.items():
    try:
        with open(f, 'r', encoding='utf-8') as fh:
            content = fh.read()
    except OSError:
        continue
    original = content
    for old_ref, new_ref in fixes:
        content = content.replace(f'`{old_ref}`', f'`{new_ref}`')
    if content != original:
        with open(f, 'w', encoding='utf-8') as fh:
            fh.write(content)
        print(f'  UPDATED: {f} ({len(fixes)} fixes)')

print('\nDone.')
