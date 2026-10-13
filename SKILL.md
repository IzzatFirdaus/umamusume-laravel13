---
name: skill-registry
description: "Pointer to the live skill registry, not a roster. The inventory is produced on demand by the refresh-skill-registry scanner, and its five scopes are a floor, not a ceiling: this file names what the scanner cannot reach and how to check it. Use when asked which skills exist, what a skill does, where skills are installed, or whether a skill is available to a given agent."
allowed-tools:
  - Read
  - Grep
  - Glob
---

# Skill Registry

This repository does not keep a skill roster. The one it used to keep was a hand-counted table that
went wrong the way all hand-counted tables go wrong: written on a single day, from a partial view of
the scopes, and then read as current long after it stopped being true.

Nothing below is a roster or a total of what is installed. The commands print the inventory; the prose
only says which roots they can and cannot reach.

## Generate the inventory

Run from the repository root so the project scopes resolve: `--project <path>` defaults to the
current directory, so it does not need spelling out.

```bash
node "$HOME/.qoder/skills/refresh-skill-registry/scripts/scan-skills.cjs" --names
node "$HOME/.qoder/skills/refresh-skill-registry/scripts/scan-skills.cjs" --violations
```

The first prints the roster grouped by scope, the second prints only the skills with problems, each
finding marked `E` error, `W` warning, `i` informational. The summary line reads
`SKILLS total=N errors=E warnings=W`. Both read each skill's own `SKILL.md` frontmatter from disk, so
they cannot drift.

The remaining flags are the ones `--help` advertises, and `--help` is the authority on that list:
`--roster` for names with descriptions, `--json` for machine-readable rows, `--strict` for errors
only, `--scope <list>` for one scope id at a time, `--desc <n>` and `--limit <n>` for output width,
and `--repair` with `--repair --yes` for the single frontmatter hazard it normalises. Raise `--limit`
before reading an empty section as "nothing here".

That total counts **directories, not distinct skills**: a skill installed into two roots is counted
twice. To see both figures:

```bash
node "$HOME/.qoder/skills/refresh-skill-registry/scripts/scan-skills.cjs" --json \
  | node -e 'let s="";process.stdin.on("data",d=>s+=d).on("end",()=>{const a=JSON.parse(s);console.log(a.length+" directories,",new Set(a.map(r=>r.name)).size+" unique names")})'
```

A skill installed as a symlink or junction is different: it is counted once, in the root holding the
link, and reported as `installer-managed -> <target>`. `--repair` never writes to those.

## The five scopes the scanner reads

| Scope id | Root |
|---|---|
| `user-global` | `~/.claude/skills` |
| `user-qoder` | `~/.qoder/skills` |
| `project-agents` | `<repo>/.agents/skills` |
| `project-qoder` | `<repo>/.qoder/skills` |
| `plugins` | `<installPath>/skills` for each entry in `~/.qoder/plugins/installed_plugins_v2.json` |

`<repo>/.qoder/skills` does not exist on this tree, so that scope contributes zero here. A scope
printing nothing means an absent root, not an empty machine, and never means "nothing installed".

## What the scanner cannot reach

Seven classes of location hold skills this scanner never prints. The figures below were measured on
this host on 2026-10-11 at commit `7efed5c`, and they describe which roots exist rather than how many
skills anyone has: re-run the command in that row before quoting its number. Each row is what the
command showed, not what a convention suggests.

| Root | Why the total misses it | Check it with |
|---|---|---|
| `~/.agents/skills` | Not a scope. The agent-neutral store: 47 of its entries reach the scanner only because `~/.qoder/skills` symlinks to them, and 13 sit in no scanned root at all | `find "$HOME/.agents/skills" -mindepth 2 -maxdepth 2 -name SKILL.md` |
| Plugin packages that lay skills out anywhere but `<installPath>/skills` | 6 of the 15 manifest entries contribute zero to the plugin scope, and the cache tree holds 153 `SKILL.md` files against the 48 that scope prints (the cache keeps every downloaded version, so neither figure is a count of installed skills) | `find "$HOME/.qoder/plugins/cache" -name SKILL.md` |
| Other user-level agent roots | The scanner names `~/.claude` and `~/.qoder` only, and these three carry 19 skills between them | `find "$HOME/.codex/skills" "$HOME/.gemini/skills" "$HOME/.opencode/skills" -mindepth 2 -maxdepth 2 -name SKILL.md` |
| Other repository-level agent roots | Only `<repo>/.agents/skills` and `<repo>/.qoder/skills` are scanned. This repo carries 28 `*/skills` roots, and `<repo>/.claude/skills` holds a skill (`playwright-cli`) that exists nowhere else on the machine | `find . -mindepth 2 -maxdepth 2 -type d -name skills` |
| `.ai/skills` | The repository's own tracked skills are not a scope; eight of them here | `git ls-files .ai/skills` |
| `<repo>/skills/` and `skills-lock.json` | The lock is gitignored and present; the `skills/` tree its `skillPath` values point into was absent when last checked, so none of those paths resolve | `git check-ignore -v skills-lock.json` |
| IDE extension bundles | Extensions ship skills to their own hosts; nothing here scans them | `find "$HOME/.vscode/extensions" -maxdepth 4 -name SKILL.md` |

WSL is not installed on this host, so there is no Linux-side root to include or rule out. On a host
that has distros, the skill roots inside them fall outside every command above as well.

A scanner total is therefore a **lower bound**. Quote it as "the five scopes", never as "all skills
installed".

## Installed, scanned, available

Three questions, three instruments:

- **Installed**: a directory holding a `SKILL.md` exists on disk. The supplemental commands answer
  this, root by root.
- **Scanned**: it lives in one of the five scope roots, so the scanner prints it.
- **Available**: the agent answering you loaded it. Only that agent's own skill listing says so. Qoder
  offers a plugin's skills only when the plugin is both listed in
  `~/.qoder/plugins/installed_plugins_v2.json` and enabled in the user's Qoder settings, Claude Code
  reads its own plugin config, the 13 `~/.agents/skills` names are available in this session while
  invisible to the scanner, `.ai/skills` reach only a host configured to read `.ai/`, and
  `<repo>/.claude/skills` only Claude Code run against this repository.

Never answer "can agent X use skill Y?" from a disk listing. Nor the reverse: a skill available to
one host can be absent from another on the same machine, so one agent's listing is not an inventory
either.

## Repository-tracked skills

```bash
git ls-files .ai/skills
git ls-files .ai/skills | wc -l
```

Those two print the set this repository is responsible for and its size, derived from git rather than
maintained by hand. Tracked skills travel with the repo, so a fresh clone carries them before any
scanner has run here. Each skill's own `SKILL.md` holds its triggers, workflow, and validation; this
file points at those and copies nothing, and it keeps no table of them, because the last one listed
one row under a sentence that said eight.

Of the 28 `*/skills` roots under this repo, 26 are gitignored local scaffolding (`AGENTS.md` §11) and
two are tracked: `.ai/skills`, and one skill under `jev-inspect/skills`. `git check-ignore -v <root>`
is the test for the first claim; `git ls-files <root>` printing nothing is not, because an untracked
directory that nobody ignored prints nothing too.

## Refresh procedure

1. `--names` for the five scopes, `--violations` for their metadata defects, `--json` piped to the
   dedupe line if the question is distinct skills.
2. The seven supplemental commands above for what those scopes miss.
3. Compare the resulting names against the asking agent's own skill listing before calling anything
   available.
4. `node "$HOME/.qoder/skills/refresh-skill-registry/scripts/self-check.cjs"` when the scanner itself
   is in question. It asserts 21 expectations over fixture skills; it passed on 2026-10-11.

## Provenance

Rewritten 2026-10-02. The frontmatter it replaced described this file as the "Complete registry of
all available skills — global installs, local project skills, and skills available in both scopes",
which it was not: it covered neither `~/.qoder/skills` nor any plugin skill. The hand-counted roster
it carried is gone by design, not by accident.

Updated 2026-10-11 by owner instruction, against the scanner and the disk rather than against
memory. The coverage section replaces a three-item list with the seven classes of location above.
`skills/` was found absent where the file had described it as a live installer-managed directory, with
its lockfile still behind. And the two dated totals the file carried as examples (205 skills, 4 errors,
62 warnings, pinned at `31592ac` on 2026-10-02; 205, 4, 61, pinned at `55be0cd` on 2026-10-06) had both
been overtaken (203, 4, 60 at `7efed5c`). No total replaces them. Two numbers pinned to shas drifted
within four days of being called an example; the command output is the inventory and a stale figure
invites comparison with nothing to compare against.

Two claims other documents hold about this file are false on inspection. Correcting them here is this
file's own business, not an edit to the rules:

- `AGENTS.md` §3 lists `SKILL.md` under generated tooling artifacts whose hand edits lose to the
  generator, and `docs/research-scratch/INDEX.md` says it "is rebuilt by `refresh-skill-registry`".
  No generator writes this path: the scanner skill's stated boundary is chat output only and never a
  registry file, nothing under `tools/` references it, and its own history is hand-committed
  (`14fdeae`, `ee199c6`). Per `AGENTS.md` §2 the code wins and the rule is stale, so this is
  documentation an agent edits when its claims go wrong rather than a file to leave alone. The other
  file in that exemption really is generated: `PRODUCT.md` carries the `impeccable:product-schema 1`
  marker and is rewritten by that plugin, so only this half of the pair was misdescribed.
- `GOVERNANCE.md` §6 pairs this file with `.agents/skills.json` as the machine-readable half of a
  skill registry. That file really exists, but its 24 entries are artisan commands driven through
  `php artisan skill:manage` (`AGENTS.md` §8, `docs/SKILL_AUTOMATION.md`), not agent skills. The pair
  is a naming collision: `.agents/skills.json` inventories nothing this document covers, and reading
  it as a skill roster is the mistake the last hand-counted table made.

Still open, and the owner's to settle rather than this file's: a skill named `skill-registry` is
installed in the user scope at `~/.qoder/skills/skill-registry`, and its own description already marks
it a deprecated static pointer. Nothing loads the copy at this path, because a repository root is not
one of the five skill scopes, so this file has always been documentation and never an installed skill.
Whether to delete it and repoint `GOVERNANCE.md`, or keep it as this pointer, is a naming decision.
