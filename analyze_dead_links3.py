#!/usr/bin/env python
"""Enumerate all dead markdown links and classify as renamed vs deleted."""

import io
import os
import re
import subprocess
import sys
import json

REPO = os.path.dirname(os.path.abspath(__file__))
MASTERS_DIR = "docs/research-scratch"
EXEMPT_PREFIXES = (".ai/", "docs/adr/")

def git(*args):
    return subprocess.run(["git", "-c", "core.quotepath=false", *args], cwd=REPO,
                          capture_output=True, text=True, encoding="utf-8", errors="replace").stdout

def md_files():
    return [p for p in git("ls-files", "--", "*.md").splitlines() if p.strip()]

def dead_links():
    bad = []
    unreadable = []
    listing = set(git("ls-files").splitlines())
    tracked_md_names = {os.path.basename(p) for p in listing if p.endswith(".md")}
    for f in md_files():
        near = os.path.dirname(f)
        try:
            text = io.open(os.path.join(REPO, f), encoding="utf-8", errors="replace").read()
        except OSError:
            unreadable.append(f)
            continue
        for n, line in enumerate(text.split("\n"), 1):
            for ref in re.findall(r"`((?:[\w./&-]+/)?[\w.-]+\.md)`", line):
                cand = ref.replace("\\", "/").lstrip("/")
                tries = [cand]
                if "/" not in cand:
                    tries += [f"docs/{cand}", f"{MASTERS_DIR}/{cand}",
                              f"{near}/{cand}" if near else cand]
                else:
                    tries += [f"docs/{cand}", f"{near}/{cand}" if near else cand]
                if any(t in listing for t in tries):
                    continue
                if "/" not in cand and cand in tracked_md_names:
                    continue
                target = next((t for t in tries if os.path.exists(os.path.join(REPO, t))), None)
                bad.append((f, n, ref, "UNTRACKED" if target else "GONE"))
    return bad, unreadable

def main():
    files = md_files()
    dead, unreadable = dead_links()
    
    # Get all existing md files for rename matching
    existing_md = set()
    for root, dirs, fs in os.walk('.'):
        dirs[:] = [d for d in fs if d not in ['.git', 'node_modules', 'vendor', 'storage', '.scratch-uma']]
        for f in fs:
            if f.endswith('.md'):
                path = os.path.join(root, f).replace('./', '').replace('\\', '/')
                existing_md.add(path)
    
    stub_pointers = {'CONSTRAINTS.md', 'PLAN.md'}
    
    renamed = []
    deleted = []
    stub_cites = []
    
    for f, n, ref, kind in dead:
        target = ref.strip()
        
        if target in stub_pointers:
            stub_cites.append((f, n, ref, kind))
            continue
        
        found_as_is = target in existing_md or target + '.md' in existing_md
        
        if not found_as_is:
            target_filename = os.path.basename(target)
            matches = [p for p in existing_md if os.path.basename(p) == target_filename]
            
            if matches:
                renamed.append((f, n, ref, kind, matches[0]))
            else:
                deleted.append((f, n, ref, kind))
    
    print(f'Total dead links: {len(dead)}')
    print(f'  Renamed candidates: {len(renamed)}')
    print(f'  Deleted (no match): {len(deleted)}')
    print(f'  Stub-pointer cites (CONSTRAINTS.md, PLAN.md): {len(stub_cites)}')
    print(f'  Unreadable: {len(unreadable)}')
    print()
    
    print('=== RENAMED CANDIDATES (by target) ===')
    from collections import Counter
    target_counts = Counter(t for _, _, _, _, t in renamed)
    for target, count in target_counts.most_common(30):
        print(f'{count:4}x {target}')
    
    print()
    print('=== DELETED (no rename match, by target) ===')
    target_counts2 = Counter(t for _, _, t, _ in deleted)
    for target, count in target_counts2.most_common(30):
        print(f'{count:4}x {target}')
    
    print()
    print('=== STUB-POINTER CITES (leave alone) ===')
    target_counts3 = Counter(t for _, _, t, _ in stub_cites)
    for target, count in target_counts3.most_common(10):
        print(f'{count:4}x {target}')
    
    # Save detailed results
    results = {
        'total': len(dead),
        'renamed': [{'file': f, 'line': n, 'ref': ref, 'kind': kind, 'match': m} for f, n, ref, kind, m in renamed],
        'deleted': [{'file': f, 'line': n, 'ref': ref, 'kind': kind} for f, n, ref, kind in deleted],
        'stub_cites': [{'file': f, 'line': n, 'ref': ref, 'kind': kind} for f, n, ref, kind in stub_cites],
    }
    with open('dead_links_analysis.json', 'w') as fh:
        json.dump(results, fh, indent=2)
    print()
    print('Detailed results saved to dead_links_analysis.json')

if __name__ == '__main__':
    sys.exit(main())
