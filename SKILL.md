---
name: skill-registry
description: "Pointer to the live skill registry, not a roster. The current inventory is produced on demand by the refresh-skill-registry scanner; this file deliberately holds no counts because a hand-written table here was stale within days. Use when asked which skills exist, what a skill does, or where skills are installed."
allowed-tools:
  - Read
  - Grep
  - Glob
---

# Skill Registry

This repository does not keep a skill roster. The one it used to keep was a hand-counted table
that went wrong the way all hand-counted tables go wrong: it was written on a single day, from a
partial view of the scopes, and then read as current long after it stopped being true.

## How to get the real list

```bash
node "$HOME/.qoder/skills/refresh-skill-registry/scripts/scan-skills.cjs" --project "$(pwd)" --names
node "$HOME/.qoder/skills/refresh-skill-registry/scripts/scan-skills.cjs" --project "$(pwd)" --violations
```

The first prints the roster grouped by scope, the second prints only the skills with problems.
The summary line reads `SKILLS total=N errors=E warnings=W`. Both read each skill's own
`SKILL.md` frontmatter from disk, so they cannot drift.

Five scopes are covered: `~/.claude/skills`, `~/.qoder/skills`, `<project>/.agents/skills`,
`<project>/.qoder/skills`, and the plugin skills named in
`~/.qoder/plugins/installed_plugins_v2.json`. A count that omits any of those five is the defect
this file used to have.

## State at the time of writing

Measured 2026-10-02 at commit `31592ac`, with the scanner above: **205 skills, 4 errors,
62 warnings**. Pinned to a sha on purpose. It is an example of what the command returns, not a
baseline to compare against, and it is wrong the moment a skill is installed or removed. The
durable facts above are the five scopes and the command; this line is a dated measurement.

## Provenance

Rewritten 2026-10-02. The previous frontmatter described this file as the "Complete registry of
all available skills — global installs, local project skills, and skills available in both
scopes", which it was not: it covered neither `~/.qoder/skills` nor any plugin skill. The
hand-counted roster it carried is gone by design, not by accident, and `GOVERNANCE.md` still
names this path as the human-readable half of the skill-registry pair.

Two things follow from that, and both are the owner's to settle rather than this file's:

1. A skill named `skill-registry` is also installed in the user scope at
   `~/.qoder/skills/skill-registry`, and its own description already marks it a deprecated static
   pointer. Nothing loads this copy: a repository root is not one of the five skill scopes, so the
   file here has always been documentation, never an installed skill. Whether to delete it and
   repoint `GOVERNANCE.md`, or keep it as this pointer, is a naming decision for the owner.
2. `PRODUCT.md` beside it is owned by the `impeccable` plugin and regenerates itself. Neither of
   these two root files is documentation to consolidate, which is why `INDEX.md` exempts them by
   type.
