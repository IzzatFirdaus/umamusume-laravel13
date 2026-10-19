# Skill Registry

A pointer, not a roster: the commands below print the inventory, and the prose says which roots they
reach. No hand-counted table lives here — every figure that appears sits beside the command that
re-derives it.

## 1. Run first

Run from the repository root so the project scopes resolve: `--project <path>` defaults to the
current directory, so it does not need spelling out.

```bash
node "$HOME/.qoder/skills/refresh-skill-registry/scripts/scan-skills.cjs" --names
node "$HOME/.qoder/skills/refresh-skill-registry/scripts/scan-skills.cjs" --violations
```

`--names` prints the roster grouped by scope; `--violations` prints only the skills with problems,
each finding marked `E` error, `W` warning, `i` informational. The summary line reads
`SKILLS total=N errors=E warnings=W`. Both read each skill's own `SKILL.md` frontmatter from disk, so
they cannot drift.

Distinct skills rather than directories:

```bash
node "$HOME/.qoder/skills/refresh-skill-registry/scripts/scan-skills.cjs" --json \
  | node -e 'let s="";process.stdin.on("data",d=>s+=d).on("end",()=>{const a=JSON.parse(s);console.log(a.length+" directories,",new Set(a.map(r=>r.name)).size+" unique names")})'
```

`--help` is the authority on the remaining flags: `--roster` for names with descriptions, `--json`
and `--pretty` for machine-readable rows, `--strict` for errors only, `--scope` for a
comma-separated list of scope ids (for example `--scope user-global,user-qoder` scans both),
`--desc <n>` and `--limit <n>` for output width, and `--repair` with `--repair --yes` for the one
frontmatter normalisation described in section 5. Raise `--limit` before reading an empty section as
"nothing here".

## 2. Reading the output

The five scopes and their roots:

| Scope id | Root |
|---|---|
| `user-global` | `~/.claude/skills` |
| `user-qoder` | `~/.qoder/skills` |
| `project-agents` | `<repo>/.agents/skills` |
| `project-qoder` | `<repo>/.qoder/skills` |
| `plugins` | `<installPath>/skills` for each entry in `~/.qoder/plugins/installed_plugins_v2.json` |

- **Directories vs unique names.** The total counts directories, so a skill installed into two roots
  is counted twice; the dedupe pipe in section 1 prints both figures side by side.
- **Symlink counting.** A skill installed as a symlink or junction is counted once, in the root
  holding the link, and reported as `installer-managed -> <target>`; `--repair` never writes to
  those.
- `<repo>/.qoder/skills` does not exist on this tree, so that scope contributes zero here.
  **A scope printing nothing means an absent root, not an empty machine, and never means "nothing
  installed."**
- The scanner total is a **lower bound**: quote it as "the five scopes", never as "all skills
  installed".

## 3. Coverage gaps

Seven classes of location sit outside the scopes; check each with the command in its row:
[coverage.md](coverage.md).

## 4. Installed, scanned, available

Three questions, three instruments:

- **Installed**: a directory holding a `SKILL.md` exists on disk. The commands in [coverage.md](coverage.md)
  answer this, root by root.
- **Scanned**: it lives in one of the five scope roots, so the scanner prints it.
- **Available**: the agent answering you loaded it. Only that agent's own skill listing says so. Qoder
  offers a plugin's skills only when the plugin is both listed in
  `~/.qoder/plugins/installed_plugins_v2.json` and enabled in the user's Qoder settings; Claude Code
  reads its own plugin config; skills that live only under `~/.agents/skills` are available in this
  session while invisible to the scanner; `.ai/skills` reach only a host configured to read `.ai/`;
  `<repo>/.claude/skills` serves only Claude Code run against this repository.

Answer "can agent X use skill Y?" from agent X's own skill listing — a disk listing answers only
installed and scanned. The reverse also holds: one agent's listing is no inventory either, since a
skill available to one host can be absent from another on the same machine.

## 5. Frontmatter violations and `--repair`

Run `--violations` first; each finding's `E`/`W`/`i` marker says how badly it breaks a reader.

`--repair` normalises exactly one frontmatter hazard: a `description` that a strict YAML parser can
misread — a wrapped plain scalar, or an unquoted value containing `": "`. Name problems are reported,
never rewritten (folder-vs-name is a human call), and plugin cache and installer-managed symlinks are
never touched. `--repair` is a dry run; `--repair --yes` applies it and backs originals up under
`~/.qoder/tmp`.

When the scanner itself is in question:

```bash
node "$HOME/.qoder/skills/refresh-skill-registry/scripts/self-check.cjs"
```

It asserts 21 expectations over fixture skills and passes on this host.

## 6. Provenance

- Hand-maintained documentation: no generator writes this path; the scanner's boundary is chat
  output, and it writes no registry file.
- Not generated, whatever `AGENTS.md` §3 says: that rule is stale — `AGENTS.md` §2 (the code wins)
  governs, so agents edit this file when its claims go wrong.
- Renamed from `SKILL.md` with its frontmatter removed (a repository root is not one of the five
  skill scopes); the rewrite history lives in [CHANGELOG.md](CHANGELOG.md), and the coverage table
  lives in [coverage.md](coverage.md).
