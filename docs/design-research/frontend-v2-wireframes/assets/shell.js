/* Trainer Desk v2.0.0 wireframe shell — sidebar, topbar, page head, mobile bar.
   Reference artifact. Mirrors resources/js/layouts/AppLayout.vue's real ten destinations
   and the design-2.0 §41 mobile bar (Home / Career / Legacy / Deck / More). */

const NAV = [
    { group: 'Play', items: [
        { label: 'Dashboard', href: 'index.html', icon: 'home', bar: 'Home' },
        { label: 'New Career', href: 'career-scenario.html', icon: 'flag', bar: null },
    ]},
    { group: 'My data', items: [
        { label: 'Careers', href: 'runs-index.html', icon: 'list', bar: 'Career' },
        { label: 'Legacy Lab', href: 'legacy.html', icon: 'network', bar: 'Legacy' },
        { label: 'Veterans', href: 'veterans.html', icon: 'trophy', bar: null },
        { label: 'Review', href: 'review.html', icon: 'triangle', bar: 'Deck' },
    ]},
    { group: 'Library', items: [
        { label: 'Support Cards', href: 'support-cards.html', icon: 'cards', bar: null },
        { label: 'Skills', href: 'skills.html', icon: 'spark', bar: null },
    ]},
    { group: 'System', items: [
        { label: 'Database', href: 'database.html', icon: 'book', bar: 'More' },
        { label: 'Settings', href: 'preferences.html', icon: 'gear', bar: null },
    ]},
];

function esc(s) {
    return String(s).replace(/[&<>"]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
}

/**
 * Build the whole page.
 * @param {object} o
 * @param {string} o.file        current file name, for the active nav state
 * @param {string} o.eyebrow     small label above the h1
 * @param {string} o.title       page h1
 * @param {string} [o.lede]      one-line description under the h1
 * @param {string} [o.crumb]     top-bar breadcrumb (already-escaped HTML allowed)
 * @param {string} [o.actions]   HTML for the page-head action slot
 * @param {string} o.body        page body HTML (goes inside .canvas)
 * @param {string} [o.bodyClass] extra class on .canvas
 * @param {boolean} [o.mock]     show the MOCK DATA banner
 * @param {string} [o.mockText]  banner copy
 * @param {string[]} [o.extraCss] additional <style> block
 */
function wfPage(o) {
    const side = NAV.map((g) => {
        const items = g.items.map((it) => {
            const active = it.href === o.file;
            return `<a class="nav-item${active ? ' active' : ''}" href="${it.href}"${active ? ' aria-current="page"' : ''}>
        <span class="dot" aria-hidden="true"></span><span>${esc(it.label)}</span></a>`;
        }).join('\n      ');
        return `      <div class="rail-group">${esc(g.group)}</div>\n      ${items}`;
    }).join('\n');

    const bars = [
        { label: 'Home', href: 'index.html' },
        { label: 'Career', href: 'runs-index.html' },
        { label: 'Legacy', href: 'legacy.html' },
        { label: 'Deck', href: 'review.html' },
        { label: 'More', href: 'database.html' },
    ].map((b) => `<a href="${b.href}"${b.href === o.file ? ' class="active"' : ''}><span class="dot" aria-hidden="true"></span>${b.label}</a>`).join('\n    ');

    return `<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>${esc(o.title)} · Trainer Desk wireframe</title>
<link rel="stylesheet" href="assets/shell.css">
${(o.extraCss || []).map((c) => `<style>${c}</style>`).join('\n')}
<!-- Pre-paint resolution, mirroring the app: the theme reaches first paint server-side
     (AppServiceProvider composes $theme, app.blade.php prints data-theme), and the layout
     width comes from the stored preference (useLayout.ts:45 -> standard when unset).
     The localStorage keys here are a static-artifact concession; the app stores both
     server-side and PRD §6.12 cuts browser storage as a second source of truth. -->
<script>(function(){try{var s=localStorage.getItem('td-wireframe-theme');if(s!=='light'&&s!=='dark'){s=window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light';}document.documentElement.dataset.theme=s;}catch(e){document.documentElement.dataset.theme='light';}try{var L=localStorage.getItem('td-wireframe-layout');document.documentElement.dataset.layout=(L==='compact'||L==='wide'||L==='standard')?L:'standard';}catch(e){document.documentElement.dataset.layout='standard';}})();</script>
</head>
<body>
${o.mock ? `<div class="mockbar">MOCK DATA · ${esc(o.mockText || 'no live run exists for this screen; layout is real, figures are illustrative')}</div>` : ''}
<div class="app">
  <aside class="rail" aria-label="Primary">
    <div class="rail-top">
      <div class="rail-brand"><span class="mk" aria-hidden="true">TD</span><span class="tx">TRAINER DESK</span></div>
${side}
    </div>
    <a class="rail-foot" href="database-sources.html">Data sources</a>
  </aside>

  <div class="main">
    <header class="topbar">
      <div class="topbar-in">
        <span class="crumb">${o.crumb || ''}</span>
        <span class="spacer"></span>
        <span class="who"><span class="pill pill-positive">Active</span></span>
      </div>
    </header>

    <div class="phead">
      <div class="eyebrow">${esc(o.eyebrow)}</div>
      <div class="hrow">
        <div>
          <h1>${esc(o.title)}</h1>
          ${o.lede ? `<p class="lede">${o.lede}</p>` : ''}
        </div>
        ${o.actions ? `<div style="display:flex;gap:8px;flex-wrap:wrap">${o.actions}</div>` : ''}
      </div>
      <div class="hr"></div>
    </div>

    <div class="canvas${o.bodyClass ? ' ' + o.bodyClass : ''}">
${o.body}
    </div>
  </div>
</div>

<nav class="mbar" aria-label="Primary mobile">
    ${bars}
</nav>
</body>
</html>`;
}

/** The annotation key block every screen ends with. */
function wfKey(title, items, extraHtml = '') {
    const lis = items.map((i) => `<li>${i}</li>`).join('\n      ');
    return `<div class="keybox">
  <h2>${esc(title)}</h2>
  <ol>${lis ? `\n      ${lis}\n  ` : ''}</ol>
  ${extraHtml}
  <p style="margin:12px 0 0;font-size:12px;color:var(--ink-muted)">
    Wireframe only. No component, token, copy string, gate or test was changed. Values are labelled at their
    source; see the collection's <code>README.md</code>.
  </p>
</div>`;
}

export { wfPage, wfKey, NAV };
