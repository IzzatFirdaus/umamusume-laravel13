#!/usr/bin/env python
"""Fix renamed path citations: repoint dead links to existing files."""

import json
import re

with open('dead_links_analysis.json') as f:
    data = json.load(f)

# Map from old ref to new ref
replacements = {
    './DESIGN.md': 'DESIGN.md',
    './README.md': 'README.md',
    'docs/design-research/DESIGN.md': 'DESIGN.md',
    'design-research/DESIGN.md': 'DESIGN.md',
    '.scratch-uma/README.md': 'README.md',
    'docs/frontend-review/2026-09-28/README.md': 'README.md',
    '.agents/README.md': 'README.md',
    'capture/README.md': 'README.md',
    'screenshot-registry/README.md': 'README.md',
    'docs/research-scratch-source/README.md': 'README.md',
}

# Group by file
by_file = {}
for r in data['renamed']:
    f = r['file']
    if f not in by_file:
        by_file[f] = []
    by_file[f].append((r['ref'], replacements.get(r['ref'], r['ref'])))

print(f'Files to fix: {len(by_file)}')
total_fixes = 0
for f, fixes in by_file.items():
    print(f'  {f}: {len(fixes)} fixes')
    total_fixes += len(fixes)
print(f'Total fixes: {total_fixes}')

# Apply fixes
for f, fixes in by_file.items():
    try:
        with open(f, 'r', encoding='utf-8') as fh:
            content = fh.read()
    except OSError as e:
        print(f'  ERROR reading {f}: {e}')
        continue
    
    original = content
    for old_ref, new_ref in fixes:
        # Replace backticked reference
        content = content.replace(f'`{old_ref}`', f'`{new_ref}`')
    
    if content != original:
        with open(f, 'w', encoding='utf-8') as fh:
            fh.write(content)
        print(f'  UPDATED: {f}')

print()
print('Done. Now re-run DocCitationParity to verify.')
