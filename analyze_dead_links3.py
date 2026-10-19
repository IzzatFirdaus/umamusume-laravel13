#!/usr/bin/env python
"""Enumerate all dead markdown links and classify as renamed vs deleted."""

import os
import sys

# The census is the one resolver. This script used to carry its own copy, and the copy
# disagreed with the tool: it had no catalogue exclusion, so it counted the paths listed in
# the catalogue files as dead links, and it read the working tree while the census measured
# a ref. Two numbers for one question, and the wrong one was the louder.
REPO = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, os.path.join(REPO, "tools"))
import doc_census as dc  # noqa: E402


def main():
    files = dc.md_files()
    dead, unreadable = dc.dead_links()

    # The files that exist, from the same subject the dead links were measured against, so a
    # rename is looked for in the tree the citation was read from rather than in whatever
    # happens to be on this machine.
    existing_md = {p for p in dc.subject_paths() if p.endswith(".md")}

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
