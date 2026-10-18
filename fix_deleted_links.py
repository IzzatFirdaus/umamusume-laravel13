#!/usr/bin/env python
"""Fix deleted-file links: repoint to masters where content moved, remove where truly gone."""

import json
import re

with open('dead_links_analysis.json') as f:
    data = json.load(f)

# Mapping: old target -> new target (for content that moved)
REPOINT_MAP = {
    'docs/design-research/CONSTRAINTS.md': 'docs/research-scratch/DESIGN-CORPUS.md',
    'docs/GATE-REGISTRY.md': 'docs/research-scratch/GOVERNANCE.md',
    'docs/PRE-MORTEM.md': 'docs/research-scratch/GOVERNANCE.md',
    'docs/SOURCE-OF-TRUTH.md': 'docs/research-scratch/GOVERNANCE.md',
    'docs/Our-Grand-Concert-plan.md': 'docs/proposals/Our-Grand-Concert-plan.md',
}

# Group deleted items by file
by_file = {}
for r in data['deleted']:
    f = r['file']
    ref = r['ref']
    if ref in REPOINT_MAP:
        new_ref = REPOINT_MAP[ref]
        if f not in by_file:
            by_file[f] = []
        by_file[f].append((ref, new_ref))

print(f'Files with repointable deleted links: {len(by_file)}')
total = sum(len(v) for v in by_file.values())
print(f'Total repoints: {total}')
for f, fixes in sorted(by_file.items()):
    print(f'  {f}: {len(fixes)}')

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
        content = content.replace(f'`{old_ref}`', f'`{new_ref}`')
    
    if content != original:
        with open(f, 'w', encoding='utf-8') as fh:
            fh.write(content)
        print(f'  UPDATED: {f}')

print()
print('Done. Re-run census to verify.')
