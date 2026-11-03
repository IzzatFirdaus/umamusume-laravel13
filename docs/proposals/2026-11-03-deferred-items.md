# Deferred items (§1.5 reconciliation)

Date: 2026-11-03 (completion pass rev 2)

## Five deferred items

The reconciliation pass (2026-11-03) left five items un-landed. Three are blocked on the peer
session's in-flight work; two are deferred pending owner input.

| Item | State | Cause | Unblocker |
| --- | --- | --- | --- |
| A | Blocked | peer-session conflict (concurrent dispatch; uncommitted `docs/` → `docs/proposals/` rename) | peer push lands; the whole series moves together |
| B | Deferred | not reached in this pass | owner ruling |
| C | Deferred | not reached in this pass | owner ruling |
| D | Blocked | peer-session conflict | peer push lands; the whole series moves together |
| E | Blocked | peer-session conflict | peer push lands; the whole series moves together |

## Cause

A concurrent session was executing the same dispatch and had the `docs/` → `docs/proposals/` rename
uncommitted (3 deleted + 3 untracked). Landing A, D or E here would have collided with that
in-flight work, so they were held back rather than risk a partial merge.

## What unblocks each

- **A, D, E:** the peer push must land first, including `9a61197f` (which is not self-contained —
  it depends on `6f3d5a81` and `e4786df4`), so the series moves as a unit.
- **B, C:** an owner ruling on the deferred scope; documented here rather than silently dropped.
