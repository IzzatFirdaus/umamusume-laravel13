#!/usr/bin/env python
"""Find and fix remaining renamed-path dead links."""

import json
import os
import re

with open('dead_links_analysis.json') as f:
    data = json.load(f)

# Get all existing md files
existing_md = set()
for root, dirs, fs in os.walk('.'):
    dirs[:] = [d for d in dirs if d not in ['.git', 'node_modules', 'vendor', 'storage', '.scratch-uma']]
    for f_name in fs:
        if f_name.endswith('.md'):
            path = os.path.join(root, f_name).replace('./', '').replace('\\', '/')
            existing_md.add(path)

# Find deleted items that could be renames (basename exists elsewhere)
stub_pointers = {'CONSTRAINTS.md', 'PLAN.md'}
new_renames = []

for r in data['deleted']:
    ref = r['ref']
    if ref in stub_pointers:
        continue
    target_filename = os.path.basename(ref)
    matches = [p for p in existing_md if os.path.basename(p) == target_filename and p != ref]
    if matches:
        new_renames.append((r['file'], r['line'], ref, matches[0]))

print(f'Additional rename candidates: {len(new_renames)}')
from collections import Counter
target_counts = Counter(t for _, _, _, t in new_renames)
for target, count in target_counts.most_common(20):
    print(f'  {count}x -> {target}')

# Apply fixes
replacements = {}
for _, _, old_ref, new_ref in new_renames:
    replacements[old_ref] = new_ref

by_file = {}
for r in data['deleted']:
    ref = r['ref']
    if ref in replacements:
        f = r['file']
        if f not in by_file:
            by_file[f] = []
        by_file[f].append((ref, replacements[ref]))

print(f'\nFiles to fix: {len(by_file)}')
total = sum(len(v) for v in by_file.values())
print(f'Total additional repoints: {total}')

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

print('\nDone.')
