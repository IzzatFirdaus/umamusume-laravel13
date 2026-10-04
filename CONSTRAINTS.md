# CONSTRAINTS.md

**Pointer. The quality bar moved on 2026-10-03. Its binding text is now
`docs/research-scratch/GOVERNANCE.md`, section "CONSTRAINTS.md (root quality bar, C-1 to C-9)".**

Read that section before writing code, exactly as you read this file before. Do not weaken a
threshold to make a change pass; relaxation requires the human owner (AGENTS.md escalation path 4).
The C-1 to C-9 table, the Floor list, the C-4 lore banned patterns and the hand-off verification
sequence are unchanged and now live in that one place.

Nothing is duplicated here on purpose. A second copy of the bar is the drift that `C-1` and
`tests/Feature/DocCitationParityTest.php` exist to catch, so this file carries a pointer and no
rules. To change a threshold, change the embedded section and add one line here saying what moved.

Gate precedence is unchanged. R-6 still puts the bar first and `docs/research-scratch/GOVERNANCE.md`
§"GATE-REGISTRY.md" still names this file and that registry as the joint source of truth for the
global gates; both statements now resolve through this stub to the same text.

The file stays at the repository root because 69 tracked files cite `CONSTRAINTS.md` by name,
including `AGENTS.md`, `CLAUDE.md`, `README.md`, `Makefile`, `tools/lore.php`, `tools/gate.py`,
eleven ADRs, a migration, two Blade views, and nine tests. Do not delete it. Do not add rules to it.
