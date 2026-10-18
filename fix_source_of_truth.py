#!/usr/bin/env python
"""Repoint bare-name SOURCE-OF-TRUTH.md citations to GOVERNANCE.md."""

import subprocess, os, re

REPO = os.path.dirname(os.path.abspath(__file__))

def git(*args):
    return subprocess.run(["git", "-c", "core.quotepath=false", *args], cwd=REPO,
                          capture_output=True, text=True, encoding="utf-8", errors="replace").stdout

def md_files():
    return [p for p in git("ls-files", "--", "*.md").splitlines() if p.strip()]

count = 0
for f in md_files():
    near = os.path.dirname(f)
    try:
        with open(os.path.join(REPO, f), encoding='utf-8', errors='replace') as fh:
            text = fh.read()
    except OSError:
        continue
    
    original = text
    # Replace bare `SOURCE-OF-TRUTH.md` with `docs/research-scratch/GOVERNANCE.md`
    # Only replace when not already part of a longer path
    text = re.sub(r'`SOURCE-OF-TRUTH\.md`', r'`docs/research-scratch/GOVERNANCE.md`', text)
    
    if text != original:
        with open(os.path.join(REPO, f), 'w', encoding='utf-8') as fh:
            fh.write(text)
        count += 1
        print(f'  UPDATED: {f}')

print(f'\nTotal files updated: {count}')
