#!/usr/bin/env python3
"""Audit a markdown status register: header spellings, index/body agreement, line endings.

Read-only. Prints a census and every contradiction; changes nothing.

Usage:
    python scripts/audit_status_register.py KNOWN-ISSUES.md
    python scripts/audit_status_register.py KNOWN-ISSUES.md --index-lines 140

Exit 0 when every body has an index row and no unannotated contradiction exists, 1 otherwise.
"""

from __future__ import annotations

import argparse
import re
import sys
from collections import Counter

HEADING = re.compile(r'^(#{2,4})\s*([A-Z][A-Z0-9]*-\d+)\b')
H_SPLIT = re.compile(r'^\*\*(State|Status)[^*]*:\*\*\s*(.*)$')
H_INNER = re.compile(r'^\*\*(State|Status):\s*(.+?)\*\*\s*(.*)$')
# An optional list marker: the index's rows are Markdown bullets in some sections and bare lines
# in others. Without the `[-*]?` allowance two closed rows (KI-104, KI-106) were dropped by the
# parser and reported as 'NO INDEX ROW' although both were present in the file all along.
INDEX_ROW = re.compile(r'^[-*]?\s*([A-Z][A-Z0-9]*-\d+)\s+([A-Za-z][A-Za-z0-9\-]*)')

# A family is matched by PREFIX. Exact-string sets report every variant as a contradiction.
CLOSED_FAMILY = ('CLOSED', 'RESOLVED', 'SUPERSEDED', 'FIXED', 'TERMINAL', 'ACKNOWLEDGED', 'ACCEPTED')
# Annotations that make a disagreement deliberate rather than a defect.
RECONCILED = ('index records closed', 'index open and this body agree', 'the index records closed')
CLOSURE_MARKER = re.compile(r'\b(CLOSED|RESOLVED|FIXED IN TREE|TERMINAL|SUPERSEDED)\b')


def family(value: str | None) -> str:
    if not value:
        return 'MISSING'
    upper = re.sub(r'^\*\*', '', value).strip().upper()
    if upper.startswith('OPEN'):
        return 'OPEN'
    if upper.startswith('PROPOSED'):
        return 'PROPOSED'
    for prefix in CLOSED_FAMILY:
        if upper.startswith(prefix):
            return 'CLOSED'
    return 'OTHER:' + upper.split(' ')[0][:16]


def read_status(segment: list[str]) -> tuple[str | None, int | None]:
    for offset, line in enumerate(segment):
        for pattern in (H_INNER, H_SPLIT):
            match = pattern.match(line)
            if match:
                value = (match.group(2) or '') + ' ' + (match.group(3) if pattern is H_INNER else '')
                return re.sub(r'\*\*', '', value).strip(), offset
    return None, None


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument('path')
    parser.add_argument('--index-lines', type=int, default=150,
                        help='how many leading lines may hold index rows (default 150)')
    args = parser.parse_args()

    # newline='' preserves CRLF; the default read translates it and makes the file look corrupt.
    with open(args.path, encoding='utf-8', newline='') as handle:
        raw = handle.read()

    crlf = raw.count('\r\n')
    lone_lf = raw.count('\n') - crlf
    lines = raw.split('\r\n') if crlf else raw.split('\n')
    print(f'file         : {args.path}')
    print(f'lines        : {len(lines)}')
    print(f'line endings : CRLF {crlf} | lone LF {lone_lf}')
    if len(lines) == 1:
        print('FATAL: one line means a universal-newline read destroyed the split, not a corrupt file.')
        return 1

    heads = [(i, HEADING.match(line).group(2)) for i, line in enumerate(lines) if HEADING.match(line)]
    if not heads:
        print('FATAL: no entry headings matched.')
        return 1

    body: dict[str, str | None] = {}
    later_closure: dict[str, bool] = {}
    for position, (start, ident) in enumerate(heads):
        end = heads[position + 1][0] if position + 1 < len(heads) else len(lines)
        value, offset = read_status(lines[start + 1:end])
        body.setdefault(ident, value)
        if value and value.upper().startswith('OPEN') and offset is not None:
            tail = '\n'.join(lines[start + 1 + offset + 1:end])
            later_closure[ident] = bool(CLOSURE_MARKER.search(tail))

    index: dict[str, str] = {}
    for line in lines[:args.index_lines]:
        match = INDEX_ROW.match(line)
        if match:
            index[match.group(1)] = match.group(2).upper()

    # Name the spelling from the MATCH, not from a hardcoded label: H_SPLIT matches both
    # `**State:** V` and `**Status:** V`, so a fixed label would mislabel half the census.
    spellings = Counter()
    for position, (start, _ident) in enumerate(heads):
        end = heads[position + 1][0] if position + 1 < len(heads) else len(lines)
        for line in lines[start:end]:
            inner = H_INNER.match(line)
            if inner:
                spellings[f'{inner.group(1)} (value inside bold)'] += 1
                break
            split = H_SPLIT.match(line)
            if split:
                spellings[f'{split.group(1)} (colon inside bold)'] += 1
                break

    ids = sorted({int(i.split('-')[1]) for i in body}, key=int)
    gaps = [n for n in range(ids[0], ids[-1] + 1) if n not in ids]

    print(f'index rows   : {len(index)}')
    print(f'body entries : {len(heads)} headings / {len(body)} distinct ids')
    print(f'id range     : {ids[0]}..{ids[-1]} | missing numbers: {len(gaps)}'
          + (f' -> {gaps[:12]}' if gaps else ' (contiguous)'))
    print(f'spellings    : {dict(spellings)}')

    only_body = sorted(set(body) - set(index), key=lambda x: int(x.split('-')[1]))
    only_index = sorted(set(index) - set(body), key=lambda x: int(x.split('-')[1]))
    if only_body or only_index:
        print(f'NO INDEX ROW : {only_body or "none"}')
        print(f'NO BODY      : {only_index or "none"}')

    problems: list[tuple[str, str, str]] = []
    for ident in sorted(set(index) & set(body), key=lambda x: int(x.split('-')[1])):
        index_family = family(index[ident])
        value = body[ident]
        body_family = family(value)
        if body_family == 'MISSING' or body_family == index_family:
            continue
        if any(marker in (value or '').lower() for marker in RECONCILED):
            continue
        if index_family == 'CLOSED' and body_family == 'OPEN' and later_closure.get(ident):
            continue  # conforming: the body keeps history, the index owns current state
        problems.append((ident, index[ident], (value or '')[:56]))

    print(f'census       : {dict(Counter(family(v) for v in index.values()))}')
    print(f'body census  : {dict(Counter(family(v) for v in body.values()))}')
    open_ids = sorted([k for k, v in index.items() if family(v) == 'OPEN'], key=lambda x: int(x.split('-')[1]))
    print(f'OPEN ({len(open_ids)})   : {", ".join(open_ids) if open_ids else "none"}')

    if problems:
        print(f'\nCONTRADICTIONS ({len(problems)}):')
        for ident, index_value, body_value in problems:
            print(f'  {ident:<10} index={index_value:<16} body={body_value}')
    else:
        print('\nCONTRADICTIONS: none')

    clean = not problems and not only_body and not only_index
    print('\nRESULT:', 'clean' if clean else 'needs reconciliation')
    return 0 if clean else 1


if __name__ == '__main__':
    sys.exit(main())
