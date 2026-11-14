#!/usr/bin/env node
// Behavioral measurement for the agent-controls layer.
//
// Reads .tooling/logs/delegation.jsonl (written by .claude/hooks/delegation-log.mjs
// on SubagentStart) plus any runtime session logs it can find, and reports:
//   a. delegation rate (sessions that spawned a subagent / sessions total)
//   b. tool-selection distribution (bash vs filesystem vs laravel-boost vs
//      playwright vs skills vs subagents)
//   c. denial rate (PreToolUse denials + which replacement tool was named)
//   d. MCP cold-start failures (timeout references)
//
// Honesty rule: a metric the available data cannot support is reported as
// "insufficient data" with the list of log sources that would be needed.
// Nothing is invented; a null baseline stays null.
//
// Usage:
//   node .tooling/scripts/measure-behavior.mjs [--since YYYY-MM-DD] [--out <path>]
//
// Default --out: .tooling/logs/behavior-baseline.json
import { readFileSync, existsSync, readdirSync, statSync } from 'node:fs';
import { join, resolve } from 'node:path';

const args = process.argv.slice(2);
function argValue(name) {
  const i = args.indexOf(name);
  return i >= 0 && i + 1 < args.length ? args[i + 1] : undefined;
}
const since = argValue('--since'); // YYYY-MM-DD (inclusive, ISO compare)
const outPath = resolve(argValue('--out') ?? '.tooling/logs/behavior-baseline.json');
const repoRoot = resolve('.');

const isoDay = (ts) => (ts ? String(ts).slice(0, 10) : null);
const inWindow = (ts) => !since || !ts || isoDay(ts) >= since;

const report = {
  generatedAt: new Date().toISOString(),
  since: since ?? null,
  sources: {},
  metrics: {
    delegationRate: null,
    toolSelection: null,
    denialRate: null,
    mcpColdStartFailures: null
  },
  gaps: []
};

// ---------------------------------------------------------------------------
// Source 1: .tooling/logs/delegation.jsonl (SubagentStart events)
// One JSON object per line: { ts, event, parent_id, child_id, depth, task }
// ---------------------------------------------------------------------------
const delegationLogPath = join(repoRoot, '.tooling', 'logs', 'delegation.jsonl');
const delegationEvents = [];
report.sources.delegationLog = { path: '.tooling/logs/delegation.jsonl', exists: existsSync(delegationLogPath), events: 0 };
if (existsSync(delegationLogPath)) {
  for (const line of readFileSync(delegationLogPath, 'utf8').split(/\r?\n/)) {
    if (!line.trim()) continue;
    try {
      const e = JSON.parse(line);
      if (inWindow(e.ts)) delegationEvents.push(e);
    } catch { /* skip malformed lines */ }
  }
  report.sources.delegationLog.events = delegationEvents.length;
}

// ---------------------------------------------------------------------------
// Source 5: .tooling/logs/denials.jsonl (PreToolUse denial events)
// One JSON object per line: { ts, runtime, tool, command, rule, replacement, cwd }
// ---------------------------------------------------------------------------
const denialLogPath = join(repoRoot, '.tooling', 'logs', 'denials.jsonl');
const denialEvents = [];
report.sources.denialLog = { path: '.tooling/logs/denials.jsonl', exists: existsSync(denialLogPath), events: 0 };
if (existsSync(denialLogPath)) {
  for (const line of readFileSync(denialLogPath, 'utf8').split(/\r?\n/)) {
    if (!line.trim()) continue;
    try {
      const e = JSON.parse(line);
      if (inWindow(e.ts)) denialEvents.push(e);
    } catch { /* skip malformed lines */ }
  }
  report.sources.denialLog.events = denialEvents.length;
}

// ---------------------------------------------------------------------------
// Source 2: Claude Code / Kilo session logs
//   %USERPROFILE%\.claude\projects\<project-slug>\<uuid>.jsonl
// Structure observed on this host: each session file carries mostly
// attachment/queue-operation/api-request-* records; assistant records exist but
// were model:"<synthetic>" placeholders in the sampled files, i.e. they do NOT
// contain a usable tool_use stream. The script still parses tool_use blocks
// when present, and reports honestly when a source yields none.
// ---------------------------------------------------------------------------
const home = process.env.USERPROFILE ?? process.env.HOME ?? '';
const claudeProjects = join(home, '.claude', 'projects');
const projectSlug = 'd--Projects-umamusume-laravel13';
const sessionDir = join(claudeProjects, projectSlug);
const toolCounts = {};
const sessionsWithToolUse = new Set();
const sessionsTotal = new Set();
let sessionFilesScanned = 0;
let toolUseRecords = 0;
report.sources.claudeSessions = { path: `.claude/projects/${projectSlug}/`, exists: existsSync(sessionDir), files: 0, toolUseRecords: 0 };

if (existsSync(sessionDir)) {
  const files = readdirSync(sessionDir).filter((f) => f.endsWith('.jsonl'));
  report.sources.claudeSessions.files = files.length;
  for (const f of files) {
    const full = join(sessionDir, f);
    const sessionId = f.replace(/\.jsonl$/, '');
    let sawAny = false;
    let fileText;
    try { fileText = readFileSync(full, 'utf8'); } catch { continue; }
    for (const line of fileText.split(/\r?\n/)) {
      if (!line.includes('"tool_use"')) continue;
      let rec;
      try { rec = JSON.parse(line); } catch { continue; }
      if (rec?.type !== 'assistant' || !rec.message?.content) continue;
      if (!inWindow(rec.timestamp)) continue;
      sawAny = true;
      for (const block of rec.message.content) {
        if (block?.type !== 'tool_use') continue;
        toolUseRecords++;
        const name = String(block.name ?? 'unknown');
        toolCounts[name] = (toolCounts[name] ?? 0) + 1;
      }
    }
    if (sawAny) sessionsWithToolUse.add(sessionId);
    sessionsTotal.add(sessionId);
    sessionFilesScanned++;
  }
  report.sources.claudeSessions.toolUseRecords = toolUseRecords;
} else {
  report.sources.claudeSessions.note = 'directory not found';
}

// ---------------------------------------------------------------------------
// Source 3: Codex session rollouts (%USERPROFILE%\.codex\sessions\**\*.jsonl)
// Line types observed: session_meta, turn_context, response_item, event_msg,
// world_state, token_usage_record. Hook denials would surface as event_msg or
// response_item entries mentioning the hook; the live-fire test produced the
// literal reply "DENIED". Parsing is best-effort; absence is reported.
// ---------------------------------------------------------------------------
const codexDir = join(home, '.codex', 'sessions');
const codexFiles = [];
const codexToolCounts = {};
const codexDenied = [];
report.sources.codexSessions = { path: '.codex/sessions/', exists: existsSync(codexDir), files: 0 };
if (existsSync(codexDir)) {
  const walk = (dir) => {
    for (const e of readdirSync(dir)) {
      const full = join(dir, e);
      if (statSync(full).isDirectory()) walk(full);
      else if (e.endsWith('.jsonl')) codexFiles.push(full);
    }
  };
  walk(codexDir);
  report.sources.codexSessions.files = codexFiles.length;
  for (const f of codexFiles) {
    let text;
    try { text = readFileSync(f, 'utf8'); } catch { continue; }
    for (const line of text.split(/\r?\n/)) {
      if (!line.includes('"type":')) continue;
      let rec;
      try { rec = JSON.parse(line); } catch { continue; }
      const ts = rec.timestamp;
      if (!inWindow(ts)) continue;
      const payload = rec.payload ?? {};
      // tool-call surface: function_call / local_shell_call payloads
      if (payload.type === 'function_call' || payload.type === 'local_shell_call') {
        const name = String(payload.name ?? payload.action?.type ?? 'shell');
        codexToolCounts[name] = (codexToolCounts[name] ?? 0) + 1;
      }
      // denial surface: the model replying DENIED is the observed live-fire signature
      if (payload.type === 'message' && JSON.stringify(payload.content ?? '').includes('DENIED')) {
        codexDenied.push({ file: f.split(/[\\/]/).pop(), ts });
      }
    }
  }
}

// ---------------------------------------------------------------------------
// Source 4: MCP cold-start failures
// Scan the same session logs + repo logs for timeout references.
// ---------------------------------------------------------------------------
const mcpTimeoutHits = [];
const timeoutPatterns = [/MCP_SERVER_REQUEST_TIMEOUT/i, /timed out/i, /ETIMEDOUT/i];
function scanForTimeouts(text, label, ts) {
  if (!inWindow(ts)) return;
  for (const re of timeoutPatterns) {
    const m = text.match(re);
    if (m) {
      mcpTimeoutHits.push({ source: label, pattern: m[0] });
      return;
    }
  }
}
for (const f of codexFiles) {
  let text;
  try { text = readFileSync(f, 'utf8'); } catch { continue; }
  for (const line of text.split(/\r?\n/)) {
    let rec;
    try { rec = JSON.parse(line); } catch { continue; }
    if (timeoutPatterns.some((re) => re.test(line))) {
      mcpTimeoutHits.push({ source: 'codex:' + f.split(/[\\/]/).pop(), ts: rec.timestamp, excerpt: line.slice(0, 200) });
    }
  }
}

// ---------------------------------------------------------------------------
// Metrics
// ---------------------------------------------------------------------------

// a. Delegation rate — requires session-boundary knowledge the delegation log
// does not carry (it logs events, not session totals). Rate = delegated
// sessions / total sessions needs correlation between the two sources; report
// the raw counts and mark the rate unknown when either side is empty.
const totalSessions = Math.max(sessionsTotal.size, report.sources.codexSessions.files);
if (delegationEvents.length > 0 && totalSessions > 0) {
  // Conservative: count distinct parent sessions in the delegation log.
  const delegatedSessions = new Set(delegationEvents.map((e) => e.parent_id)).size;
  report.metrics.delegationRate = {
    delegatedSessions,
    totalSessions,
    rate: Number((delegatedSessions / totalSessions).toFixed(4)),
    baseline: 'unknown baseline (no pre-control measurement exists)',
    caveat: 'totalSessions counts session FILES, not verified interactive sessions; the delegation log only covers runtimes with the logging hook wired (Claude Code, Qoder, DSH). Cross-runtime correlation is approximate.'
  };
} else {
  report.metrics.delegationRate = {
    status: 'insufficient data',
    delegatedEvents: delegationEvents.length,
    sessionFiles: totalSessions,
    baseline: 'unknown baseline (no pre-control measurement exists)',
    needs: 'delegation.jsonl does not exist yet (no SubagentStart has fired since the logging hook landed). It fills automatically as Claude Code / Qoder / DSH sessions delegate. To measure a rate before then, run at least one delegation in a wired runtime.'
  };
}

// b. Tool selection distribution — only meaningful with real tool_use streams.
if (toolUseRecords > 0 || Object.keys(codexToolCounts).length > 0) {
  report.metrics.toolSelection = {
    claudeSide: toolCounts,
    codexSide: codexToolCounts,
    note: 'Counts include ALL tools. Compare bash-family (Bash/PowerShell/shell) against mcp__filesystem / mcp__laravel-boost / mcp__playwright / skill / Task usage. Codex shell calls appear as local_shell_call/function_call.'
  };
} else {
  report.metrics.toolSelection = {
    status: 'insufficient data',
    needs: 'The sampled Claude/Kilo session files contain model:"<synthetic>" placeholder assistant records with no tool_use blocks (Kilo writes its real session data elsewhere). To produce this metric, tool-use streams must be logged by each runtime: Claude Code logs exist at ~/.claude/projects/<slug>/ but were placeholder-only on this host; Codex rollouts DO carry function_call payloads (see codexSide when files exist); Hermes/Qoder/Kilo/DSH need their own session-log export to be measurable.'
  };
}

// c. Denial rate — PreToolUse denials are now logged to .tooling/logs/denials.jsonl
// by all four adapters (claude/qoder/codex/hermes pretooluse).
if (denialEvents.length > 0) {
  const totalDenials = denialEvents.length;
  const byRuntime = {};
  const byRule = {};
  const byReplacement = {};
  for (const e of denialEvents) {
    byRuntime[e.runtime] = (byRuntime[e.runtime] ?? 0) + 1;
    byRule[e.rule] = (byRule[e.rule] ?? 0) + 1;
    if (e.replacement) byReplacement[e.replacement] = (byReplacement[e.replacement] ?? 0) + 1;
  }
  // Top denied commands (by rule id, which maps to command pattern)
  const topRules = Object.entries(byRule).sort((a, b) => b[1] - a[1]).slice(0, 10).map(([rule, count]) => ({ rule, count }));
  report.metrics.denialRate = {
    status: 'ok',
    totalDenials,
    byRuntime,
    byRule,
    byReplacement,
    topDeniedCommands: topRules,
    note: 'Denials logged by all four adapters via shared denial-log.mjs. Each entry includes runtime, tool, command, matched rule id, and named replacement tool.'
  };
} else {
  report.metrics.denialRate = {
    status: 'insufficient data',
    needs: 'No denial events found in .tooling/logs/denials.jsonl (file may not exist or be empty). Run a denied command (e.g., cat composer.json) through any adapter to generate events.'
  };
}

// d. MCP cold-start failures
if (mcpTimeoutHits.length > 0) {
  report.metrics.mcpColdStartFailures = {
    count: mcpTimeoutHits.length,
    hits: mcpTimeoutHits.slice(0, 20),
    note: 'References to MCP_SERVER_REQUEST_TIMEOUT in session logs may be config echoes rather than failures; inspect excerpts.'
  };
} else {
  report.metrics.mcpColdStartFailures = {
    count: 0,
    note: 'No timeout references found in scanned logs. Caveat: the scanned surfaces (Codex rollouts) may not capture MCP connection errors for other runtimes; Hermes/Qoder/Kilo MCP logs are not in the scanned set.'
  };
}

// ---------------------------------------------------------------------------
// Output
// ---------------------------------------------------------------------------
const json = JSON.stringify(report, null, 2);
if (args.includes('--stdout')) {
  console.log(json);
} else {
  const { mkdirSync, writeFileSync } = await import('node:fs');
  const parent = outPath.replace(/[\\/][^\\/]+$/, '');
  mkdirSync(parent, { recursive: true });
  writeFileSync(outPath, json + '\n');
  console.log('written:', outPath);
  console.log(json);
}
