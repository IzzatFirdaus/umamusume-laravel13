#!/usr/bin/env python3
"""Find citations that resolve on this machine but point at a file git does not track.

The failure this exists for: a tracked document cites a path that works locally and breaks on a
fresh clone, because the file is untracked (new and never added) or gitignored (scaffolding that was
never meant to travel).

Three classes, and only one of them is this tool's problem:

  UNTRACKED     on disk, not ignored, not in git. This is the defect: the citation is followable
                here and absent for anyone who clones. Exit 1.
  CASE-MISMATCH the citation's spelling differs from the tracked path only by case. Windows resolves
                it; Linux does not. Reported, never fatal, because a historical register entry
                legitimately quotes the spelling that was true when it was written (AGENTS.md §11
                forbids rewriting a dated record to match today's tree).
  GITIGNORED    the target is ignored by design (.agents/, .hermes/, .scratch-uma/). Counted and
                summarised, not fatal: local scaffolding is not part of the repository and a
                document may name it.

Exit: 0 clean (or only exempt/known classes), 1 at least one UNTRACKED citation.
"""

import json
import os
import re
import subprocess
import sys

REPO = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))

# A target under one of these is scaffolding rather than repository content. `git check-ignore`
# is the authority; this tuple is only a fallback for paths git reports as untracked-but-not-ignored
# that are nonetheless session scratch.
SCRATCH_PREFIXES = ('.scratch-uma/', '.agents/', '.hermes/', '.claude/', '.cursor/', '.codex/',
                    '.kilo/', '.opencode/', '.worktrees/')


ALLOWLIST = os.path.join(REPO, 'tools', 'untracked-citation-allowlist.json')


def git(*args):
    return subprocess.run(['git', *args], cwd=REPO, capture_output=True, text=True).stdout


def load_allowlist() -> dict[str, str]:
    """Paths whose untracked status is known, owned and dated, not rot.

    A ratchet rather than a mute: an entry that no longer matches anything is reported, so an
    exemption cannot outlive the condition it was written for. Each entry names the file and why
    it is untracked today; deleting the entry is how the exemption ends.
    """
    try:
        with open(ALLOWLIST, encoding='utf-8') as fh:
            entries = json.load(fh)
    except (OSError, ValueError):
        return {}

    return {e['path']: e.get('reason', '') for e in entries if isinstance(e, dict) and 'path' in e}


def main() -> int:
    listing = set(git('ls-files').splitlines())
    lowered = {p.lower(): p for p in listing}
    ignored_cache: dict[str, bool] = {}

    def is_ignored(path: str) -> bool:
        if path not in ignored_cache:
            result = subprocess.run(['git', 'check-ignore', '-q', path], cwd=REPO)
            ignored_cache[path] = result.returncode == 0
        return ignored_cache[path]

    allowlist = load_allowlist()
    untracked, allowed, case_mismatch, ignored = [], [], [], []

    for doc in git('ls-files', '*.md').splitlines():
        doc = doc.strip()
        if not doc:
            continue
        near = os.path.dirname(doc)
        try:
            with open(os.path.join(REPO, doc), encoding='utf-8', errors='replace') as fh:
                body = fh.read()
        except OSError:
            continue

        for line_no, line in enumerate(body.split('\n'), 1):
            for ref in re.findall(r'`((?:[\w./&-]+/)?[\w.-]+\.md)`', line):
                candidate = ref.replace('\\', '/').lstrip('/')
                tries = [candidate]
                if '/' not in candidate:
                    tries += [f'docs/{candidate}', f'docs/research-scratch/{candidate}',
                              f'{near}/{candidate}' if near else candidate]
                else:
                    tries += [f'docs/{candidate}']

                if any(t in listing for t in tries):
                    continue

                target = next((t for t in tries if os.path.exists(os.path.join(REPO, t))), None)
                if target is None:
                    continue  # absent everywhere: doc_census.py's GONE class, not this tool's.

                if target.lower() in lowered:
                    case_mismatch.append((doc, line_no, ref, lowered[target.lower()]))
                elif is_ignored(target) or target.startswith(SCRATCH_PREFIXES):
                    ignored.append((doc, line_no, ref, target))
                elif target in allowlist:
                    allowed.append((doc, line_no, ref, target))
                else:
                    untracked.append((doc, line_no, ref, target))

    for doc, line_no, ref, resolved in untracked:
        print(f'UNTRACKED      {doc}:{line_no} -> {ref} (resolves to {resolved})')
    for doc, line_no, ref, tracked_path in case_mismatch:
        print(f'CASE-MISMATCH  {doc}:{line_no} -> {ref} (tracked as {tracked_path})')

    if ignored:
        by_root: dict[str, int] = {}
        for _, _, _, target in ignored:
            by_root[target.split('/')[0] + '/'] = by_root.get(target.split('/')[0] + '/', 0) + 1
        summary = ', '.join(f'{root} {count}' for root, count in sorted(by_root.items()))
        print(f'\nGITIGNORED: {len(ignored)} citation(s) into scaffolding ({summary}); exempt.')

    for doc, line_no, ref, target in allowed:
        print(f'ALLOWED        {doc}:{line_no} -> {ref} (untracked by design, pending owner commit)')

    # A stale exemption is worse than none: it silently widens the exemption. Report every
    # allowlist entry that no longer matches a live citation so it gets deleted.
    cited_allowed = {target for _, _, _, target in allowed}
    for path in allowlist:
        if path not in cited_allowed:
            state = 'now tracked' if path in listing else 'no longer cited or absent'
            print(f'STALE-ALLOWLIST {path} ({state}); delete its entry from '
                  f'tools/untracked-citation-allowlist.json')

    print(f'\nuntracked {len(untracked)}, allowed {len(allowed)}, '
          f'case-mismatch {len(case_mismatch)}, gitignored-exempt {len(ignored)}')

    if untracked:
        print('FAIL: a tracked document cites a file git does not track. Commit the target, or '
              'repoint the citation at a tracked path.')
        return 1

    return 0


if __name__ == '__main__':
    sys.exit(main())
