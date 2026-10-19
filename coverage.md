# Coverage gaps: locations the skill-registry scanner cannot reach

Seven classes of location sit outside the five scopes; check each with the command in its row. Every
figure below comes from the command(s) in its own row on this host — re-run that row's commands
before quoting a number. This file is the repository's versioned copy of the general form installed
with the `skill-registry` skill; it specialises that form and is authoritative for this repository.
Derived values are permitted: a figure computed from other figures in its own row is sourced when
the commands producing its inputs are in that row.

| Root | Why the total misses it | Check it with |
|---|---|---|
| `~/.agents/skills` | Not a scope. The agent-neutral store: its 60 `SKILL.md` files are counted by the first command, 47 distinct skills are reached through the links from `~/.qoder/skills`, and the other 13 sit in no scanned root at all | `find "$HOME/.agents/skills" -mindepth 2 -maxdepth 2 -name SKILL.md \| wc -l`<br>`find "$HOME/.qoder/skills" -type l -exec readlink {} \; \| grep -c "^$HOME/.agents/skills/[^/]*$"` |
| Plugin packages that lay skills out anywhere but `<installPath>/skills` | 6 of the 15 manifest entries contribute zero to the plugin scope, and the cache tree holds 153 `SKILL.md` files against the 48 that scope prints (the cache keeps every downloaded version, so neither figure is a count of installed skills) | `find "$HOME/.qoder/plugins/cache" -name SKILL.md \| wc -l`<br>`node "$HOME/.qoder/skills/refresh-skill-registry/scripts/scan-skills.cjs" --json \| node -e 'const os=require("os");let s="";process.stdin.on("data",d=>s+=d).on("end",()=>{const rows=JSON.parse(s);const m=require(os.homedir()+"/.qoder/plugins/installed_plugins_v2.json");const ids=Object.keys(m.plugins).map(k=>k.split("@")[0]);const ps=rows.filter(r=>r.scope==="plugins");const have=new Set(ps.map(r=>r.plugin));console.log(ids.length+" manifest entries, "+ids.filter(i=>!have.has(i)).length+" contributing zero, "+ps.length+" plugin-scope skills")})'` |
| Other user-level agent roots | The scanner names `~/.claude` and `~/.qoder` only, and these three carry 19 skills between them | `find "$HOME/.codex/skills" "$HOME/.gemini/skills" "$HOME/.opencode/skills" -mindepth 2 -maxdepth 2 -name SKILL.md \| wc -l` |
| Other repository-level agent roots | Only `<repo>/.agents/skills` and `<repo>/.qoder/skills` are scanned. This repo carries 28 `*/skills` roots, and `<repo>/.claude/skills` holds a skill (`playwright-cli`) that appears in no scanned root and nowhere else within four levels of the repo root | `find . -mindepth 2 -maxdepth 2 -type d -name skills \| wc -l`<br>`find . -maxdepth 4 -type d -name playwright-cli` |
| `.ai/skills` | The repository's own tracked skills are not a scope; eight here | `git ls-files .ai/skills \| wc -l` |
| `<repo>/skills/` and `skills-lock.json` | The lock is gitignored and present, and its `skillPath` values point into `skills/`; the `skills/` tree itself is absent, so none of those paths resolve | `git check-ignore -v skills-lock.json`<br>`test -d skills && echo PRESENT || echo ABSENT`<br>`node -e 'const l=require("./skills-lock.json");console.log(Object.values(l.skills).map(s=>s.skillPath).join("\n"))'` |
| IDE extension bundles | Extensions ship skills to their own hosts; nothing here scans them | `find "$HOME/.vscode/extensions" -maxdepth 4 -name SKILL.md \| wc -l` |

A scanner total is a **lower bound**: quote it as "the five scopes", never as "all skills installed".

WSL is not installed on this host — `wsl --status` reports "The Windows Subsystem for Linux is not
installed" — so there is no Linux-side root to include or rule out. On a host that has distros, the
skill roots inside them fall outside every command above as well.
