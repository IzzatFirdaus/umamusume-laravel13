/* ============================================================================
   Trainer Desk v2.0.0 — dashboard.
   The landing screen (SCR-CAR-001). Every figure below is from run 25 (Unity Cup,
   Rice Shower, Active, turn 67) and the 10 URA Finale runs in the dev database,
   read from the live Inertia props on 2026-10-21.
   ============================================================================ */
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { wfPage, wfKey } from './assets/shell.js';

const OUT = path.dirname(fileURLToPath(import.meta.url));

const runs = `            <tbody>
              <tr><td><b>Rice Shower</b><br><span style="color:var(--ink-muted)">[Rosy Dreams] · ★3</span></td>
                <td>Unity Cup</td><td class="n mono">67</td>
                <td><span class="pill pill-positive">Active</span></td>
                <td class="n"><a href="cockpit-unity-cup.html" class="btn btn-sm">Open cockpit</a></td></tr>
              <tr><td><b>Maruzensky</b></td><td>URA Finale</td><td class="n mono">75</td>
                <td><span class="pill pill-neutral">Completed</span></td>
                <td class="n"><a href="result.html" class="btn btn-sm">Open result</a></td></tr>
              <tr><td><b>Haru Urara</b></td><td>URA Finale</td><td class="n mono">69</td>
                <td><span class="pill pill-neutral">Completed</span></td>
                <td class="n"><a href="result.html" class="btn btn-sm">Open result</a></td></tr>
              <tr><td><b>Special Week</b></td><td>URA Finale</td><td class="n mono">—</td>
                <td><span class="pill pill-neutral">Retired</span></td>
                <td class="n"><a href="result.html" class="btn btn-sm">Open result</a></td></tr>
            </tbody>`;

const body = `
<div class="grid g-4" style="margin-bottom:14px">
  <div class="card" style="margin:0">
    <div style="font-size:10px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--ink-muted)">Careers running</div>
    <div style="font-size:26px;font-weight:800;color:var(--ink-strong);line-height:1.2">1</div>
    <div style="font-size:11.5px;color:var(--ink-muted)">Rice Shower · Unity Cup</div>
  </div>
  <div class="card" style="margin:0">
    <div style="font-size:10px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--ink-muted)">Careers finished</div>
    <div style="font-size:26px;font-weight:800;color:var(--ink-strong);line-height:1.2">10</div>
    <div style="font-size:11.5px;color:var(--ink-muted)">7 Completed · 3 Retired</div>
  </div>
  <div class="card" style="margin:0">
    <div style="font-size:10px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--ink-muted)">Veterans banked</div>
    <div style="font-size:26px;font-weight:800;color:var(--ink-strong);line-height:1.2">—</div>
    <div style="font-size:11.5px;color:var(--ink-muted)">not recorded</div>
  </div>
  <div class="card" style="margin:0">
    <div style="font-size:10px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--ink-muted)">Scenarios in play</div>
    <div style="font-size:26px;font-weight:800;color:var(--ink-strong);line-height:1.2">4</div>
    <div style="font-size:11.5px;color:var(--ink-muted)">Global · all permanent</div>
  </div>
</div>

<div class="grid g-side" style="margin-bottom:14px">
  <div class="card" style="margin:0">
    <h2>Pick up where you left off</h2>
    <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;padding:4px 0 6px">
      <div style="flex:1 1 240px;min-width:0">
        <div style="font-size:19px;font-weight:800;color:var(--ink-strong)">Rice Shower <span class="pill pill-accent" style="vertical-align:3px">Unity Cup</span></div>
        <div style="font-size:12.5px;color:var(--ink-muted);margin-top:2px">Senior Year · Early October · Turn 67 · Energy Low band · Mood GREAT</div>
      </div>
      <div style="border:1px solid var(--rule-soft);border-left:3px solid var(--primary-action);border-radius:var(--r-md);background:var(--raised);padding:8px 12px;display:flex;align-items:center;gap:11px">
        <div>
          <div style="font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--ink-muted)">Recommended</div>
          <div style="font-size:12px;color:var(--ink-muted)">Energy is in the low band</div>
        </div>
        <a class="btn btn-primary" href="cockpit-unity-cup.html">Rest this turn</a>
      </div>
    </div>
    <div class="note" style="margin-top:6px"><span class="g">○</span><span><b>Unity Cup finale in 5 turns.</b> Turns 68–72 remain in this career. <a href="cockpit-unity-cup.html" style="font-weight:800;text-decoration:underline">Open the cockpit</a></span></div>
  </div>

  <div class="card" style="margin:0">
    <h2>Needs you</h2>
    <div style="display:flex;flex-direction:column;gap:9px">
      <div class="tile" style="border-left:3px solid var(--risk)">
        <div class="k">Review queue</div>
        <div class="v" style="font-size:14px">2 candidates unconfirmed</div>
        <a href="review.html" style="font-size:12px;text-decoration:underline;color:var(--ink-strong)">Review them</a>
      </div>
      <div class="tile">
        <div class="k">Data sources</div>
        <div class="v" style="font-size:14px">Matrix verified 2026-09-27</div>
        <a href="database-sources.html" style="font-size:12px;text-decoration:underline;color:var(--ink-strong)">Source ledger</a>
      </div>
      <div class="tile">
        <div class="k">Import</div>
        <div class="v" style="font-size:14px">Bring in a historical run</div>
        <a href="import.html" style="font-size:12px;text-decoration:underline;color:var(--ink-strong)">Import a run</a>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <h2>Careers</h2>
  <table class="t">${runs}</table>
  <div style="display:flex;justify-content:space-between;align-items:center;margin-top:12px;flex-wrap:wrap;gap:8px">
    <span style="font-size:12px;color:var(--ink-muted)">11 runs · 4 shown</span>
    <a class="btn btn-sm" href="runs-index.html">All careers</a>
  </div>
</div>

<div class="grid g-2" style="margin-top:14px">
  <div class="card" style="margin:0">
    <h2>Scenario coverage</h2>
    <table class="t">
      <thead><tr><th>Scenario</th><th class="n">Runs</th><th>Live since</th></tr></thead>
      <tbody>
        <tr class="is-current"><td>Unity Cup</td><td class="n mono">1</td><td class="mono">2025-11-06</td></tr>
        <tr><td>URA Finale</td><td class="n mono">10</td><td class="mono">2025-06-26</td></tr>
        <tr><td>Trackblazer</td><td class="n mono">0</td><td class="mono">2026-03-12</td></tr>
        <tr><td>Our Grand Concert</td><td class="n mono">0</td><td class="mono">2026-07-22</td></tr>
      </tbody>
    </table>
    <p class="note"><span class="g">○</span><span>Trackblazer and Our Grand Concert have no run in this database, so their cockpits are drawn from the composition contract, not from a rendered run.</span></p>
  </div>

  <div class="card" style="margin:0">
    <h2>Start a new career</h2>
    <p class="sub">Six steps: scenario, trainee, build target, legacy, deck, preflight.</p>
    <div style="display:flex;flex-direction:column;gap:8px">
      <a class="btn btn-primary" href="career-scenario.html">Choose a scenario</a>
      <a class="btn" href="import.html">Import a historical run instead</a>
    </div>
  </div>
</div>

<div class="card" style="margin-top:14px">
  <h2>Table of contents</h2>
  <nav aria-label="Table of contents">
    <div class="grid g-3">
      <div>
        <div class="k" style="font-size:10px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--ink-muted)">Landing</div>
        <ul style="list-style:none;margin:6px 0 0;padding:0;display:flex;flex-direction:column;gap:3px;font-size:12.5px">
          <li><a href="index.html">Dashboard (landing)</a></li>
          <li><a href="dashboard.html">Dashboard (SCR-CAR-001)</a></li>
          <li><a href="runs-index.html">Careers</a></li>
          <li><a href="runs-create.html">New career (form)</a></li>
          <li><a href="import.html">Import a run</a></li>
          <li><a href="import-preview.html">Import preview</a></li>
        </ul>
        <div class="k" style="font-size:10px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--ink-muted);margin-top:10px">Setup wizard</div>
        <ul style="list-style:none;margin:6px 0 0;padding:0;display:flex;flex-direction:column;gap:3px;font-size:12.5px">
          <li><a href="career-scenario.html">Scenario</a></li>
          <li><a href="career-trainee.html">Trainee</a></li>
          <li><a href="career-profile.html">Profile</a></li>
          <li><a href="career-target.html">Build target</a></li>
          <li><a href="career-legacy.html">Legacy</a></li>
          <li><a href="career-deck.html">Deck</a></li>
          <li><a href="career-preflight.html">Preflight</a></li>
        </ul>
      </div>
      <div>
        <div class="k" style="font-size:10px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--ink-muted)">Cockpit</div>
        <ul style="list-style:none;margin:6px 0 0;padding:0;display:flex;flex-direction:column;gap:3px;font-size:12.5px">
          <li><a href="cockpit-unity-cup.html">Unity Cup</a></li>
          <li><a href="cockpit-ura-finale.html">URA Finale</a></li>
          <li><a href="cockpit-trackblazer.html">Trackblazer</a></li>
          <li><a href="cockpit-grand-concert.html">Our Grand Concert</a></li>
        </ul>
        <div class="k" style="font-size:10px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--ink-muted);margin-top:10px">Run lifecycle</div>
        <ul style="list-style:none;margin:6px 0 0;padding:0;display:flex;flex-direction:column;gap:3px;font-size:12.5px">
          <li><a href="training-decision.html">Training decision</a></li>
          <li><a href="race-decision.html">Race decision</a></li>
          <li><a href="career-race-planner.html">Race planner</a></li>
          <li><a href="event-decision.html">Event decision</a></li>
          <li><a href="inheritance.html">Inheritance</a></li>
          <li><a href="skills-planner.html">Skills planner</a></li>
          <li><a href="run-deck.html">Run deck</a></li>
          <li><a href="timeline.html">Timeline</a></li>
          <li><a href="result.html">Result</a></li>
          <li><a href="career-save-veteran.html">Save Veteran</a></li>
        </ul>
        <div class="k" style="font-size:10px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--ink-muted);margin-top:10px">Snapshot</div>
        <ul style="list-style:none;margin:6px 0 0;padding:0;display:flex;flex-direction:column;gap:3px;font-size:12.5px">
          <li><a href="career-snapshot-entry.html">Entry</a></li>
          <li><a href="career-snapshot-setup.html">Setup</a></li>
          <li><a href="career-snapshot-review.html">Review</a></li>
        </ul>
      </div>
      <div>
        <div class="k" style="font-size:10px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--ink-muted)">Legacy and veterans</div>
        <ul style="list-style:none;margin:6px 0 0;padding:0;display:flex;flex-direction:column;gap:3px;font-size:12.5px">
          <li><a href="legacy.html">Legacy Lab</a></li>
          <li><a href="legacy-builder.html">Legacy builder</a></li>
          <li><a href="legacy-compare.html">Compare legacies</a></li>
          <li><a href="veterans.html">Veterans</a></li>
          <li><a href="veteran-detail.html">Veteran detail</a></li>
          <li><a href="veterans-compare.html">Compare veterans</a></li>
        </ul>
        <div class="k" style="font-size:10px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--ink-muted);margin-top:10px">Library</div>
        <ul style="list-style:none;margin:6px 0 0;padding:0;display:flex;flex-direction:column;gap:3px;font-size:12.5px">
          <li><a href="catalog.html">Trainees</a></li>
          <li><a href="catalog-detail.html">Trainee detail</a></li>
          <li><a href="support-cards.html">Support cards</a></li>
          <li><a href="support-card-detail.html">Support card detail</a></li>
          <li><a href="skills.html">Skills</a></li>
          <li><a href="skill-detail.html">Skill detail</a></li>
        </ul>
        <div class="k" style="font-size:10px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--ink-muted);margin-top:10px">Review</div>
        <ul style="list-style:none;margin:6px 0 0;padding:0;display:flex;flex-direction:column;gap:3px;font-size:12.5px">
          <li><a href="review.html">Review queue</a></li>
          <li><a href="review-detail.html">Review a match</a></li>
        </ul>
      </div>
    </div>
    <div class="grid g-3" style="margin-top:10px">
      <div>
        <div class="k" style="font-size:10px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--ink-muted)">Database</div>
        <ul style="list-style:none;margin:6px 0 0;padding:0;display:flex;flex-direction:column;gap:3px;font-size:12.5px">
          <li><a href="database.html">Hub</a></li>
          <li><a href="database-sources.html">Sources</a></li>
          <li><a href="database-scenarios.html">Scenarios</a></li>
          <li><a href="database-races.html">Races</a></li>
          <li><a href="database-events.html">Events</a></li>
          <li><a href="database-shop-items.html">Shop items</a></li>
          <li><a href="database-sparks.html">Sparks</a></li>
        </ul>
      </div>
      <div>
        <div class="k" style="font-size:10px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--ink-muted)">System</div>
        <ul style="list-style:none;margin:6px 0 0;padding:0;display:flex;flex-direction:column;gap:3px;font-size:12.5px">
          <li><a href="preferences.html">Settings</a></li>
          <li><a href="help.html">Help</a></li>
          <li><a href="error-404.html">404</a></li>
          <li><a href="error-419.html">419</a></li>
          <li><a href="error-500.html">500</a></li>
        </ul>
      </div>
      <div></div>
    </div>
  </nav>
</div>

${wfKey('Annotation key · Dashboard', [
  '<b>One primary action.</b> "Rest this turn" is the only filled button on the screen; every other action is an outline or a link. That is DESIGN.md §2.3 "one primary action per screen" and Von Restorff applied honestly.',
  '<b>The goal is in the first viewport.</b> "Unity Cup finale in 5 turns" sits beside the resume card rather than inside the cockpit\'s advisor list, which is where it renders today. This is the Goal-Gradient fix from the cockpit audit, applied one level up.',
  '<b>Absence is printed, not dropped.</b> "Veterans banked — not recorded" keeps the tile and says why. The repo\'s standing rule is that an absence prints with its reason; the external prototype review\'s "drop the row" rule is applied only to <em>explanation paragraphs</em>, never to a value the screen claims to report.',
  '<b>Nav taxonomy, two levels.</b> The sidebar groups ten destinations into Play / My data / Library / System. The running app\'s sidebar is one flat ten-item list; grouping is the fix for the "at or under eight" breach recorded in <code>AppLayout.vue:178</code> without deleting a destination.',
  '<b>Mobile is a different shape, not a squeezed one.</b> Below 760px the sidebar is replaced by design-2.0 §41\'s bottom bar: Home / Career / Legacy / Deck / More.',
  '<b>No lime chrome.</b> The accent is spent on: the Active pill (<code>--status-positive</code>), the one primary action (<code>--primary-action</code>), and the review tile\'s risk edge (<code>--risk</code>). Section headings are ink plus a rule.',
])}
`;

// The sandbox forbids this script writing .html files, so the page is printed to
// stdout and captured by the agent tooling. `node build-index.js > index.html` is the
// supported invocation outside the sandbox.
process.stdout.write(wfPage({
    file: 'index.html',
    eyebrow: 'Dashboard',
    title: 'Trainer Desk',
    lede: 'One active career, ten finished runs and four Global scenarios. This is the screen you land on.',
    crumb: '<b>Dashboard</b>',
    actions: '<a class="btn btn-primary" href="career-scenario.html">New career</a><a class="btn" href="runs-index.html">All careers</a>',
    body,
}));
