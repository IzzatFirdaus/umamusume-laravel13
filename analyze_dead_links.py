import os, re

# Read census output
with open('/tmp/census_output.txt', 'r') as f:
    content = f.read()

# Extract dead links - format: '  GONE      FILE:LINE -> TARGET'
dead_links = []
for line in content.split('\n'):
    m = re.match(r'\s*(GONE|UNTRACKED)\s+([^:]+):(\d+)\s*->\s*(.+)', line)
    if m:
        dead_links.append((m.group(2), int(m.group(3)), m.group(4)))

print(f'Total dead links parsed: {len(dead_links)}')

# Get list of all existing .md files
existing_md = set()
for root, dirs, files in os.walk('.'):
    dirs[:] = [d for d in dirs if d not in ['.git', 'node_modules', 'vendor', 'storage', '.scratch-uma']]
    for f in files:
        if f.endswith('.md'):
            path = os.path.join(root, f).replace('./', '')
            path = path.replace('\\', '/')
            existing_md.add(path)

print(f'Existing .md files: {len(existing_md)}')

# Check which dead link targets exist
renamed = []
deleted = []
stub_pointers = ['CONSTRAINTS.md', 'PLAN.md']

for file, line, target in dead_links:
    target_norm = target.strip()
    
    if target_norm in stub_pointers:
        continue
    
    found = False
    if target_norm in existing_md:
        found = True
    elif target_norm + '.md' in existing_md:
        found = True
    
    if not found:
        target_filename = os.path.basename(target_norm)
        matches = [p for p in existing_md if os.path.basename(p) == target_filename]
        
        if matches:
            renamed.append((file, line, target_norm, matches[0]))
        else:
            deleted.append((file, line, target_norm))

print(f'Potential renames: {len(renamed)}')
print(f'Deleted (no match): {len(deleted)}')
print()
print('Top renames (by target frequency):')
from collections import Counter
target_counts = Counter(t for _, _, t, _ in renamed)
for target, count in target_counts.most_common(20):
    examples = [r for r in renamed if r[2] == target][:3]
    for r in examples:
        print(f'  {r[0]}:{r[1]} -> {r[2]} === {r[3]}')
    print(f'  ... total {count} citations to {target}')
