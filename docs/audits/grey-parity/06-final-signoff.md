# GP-07 Final sign-off — grey-parity audit (Phase A)

2026-11-14. Verdict: **`audit stands with erratum`** (errata below; 00-05 left as delivered).
Tree: HEAD unchanged at `d8238c87`; `app.css`, `shell.css`, `DesignTokensTest.php` unmoved since
the audit (`git log d8238c87..HEAD` on all three: empty); peer rows unchanged.

Deliverables: all six intact — 00:62, 01:84, 02:51, 03:66, 04:94 (GP-01..10 field-complete, six
commits cited), 05:31 (16 rows == 16 files on disk); total 388. No peer edits since delivery.

Six spot-checks, recomputed from current `app.css` hex (inputs = linearised L):

| Figure | Computed | Audit | Verdict |
|---|---|---|---|
| panel→sunken | 1.130 (0.9660/0.8492) | 1.130 | MATCH |
| page→panel | 1.057 (0.9660/0.9115) | 1.057 | MATCH |
| rule on page | 3.093 (0.2609/0.9115) | 3.093 | MATCH |
| ink on panel | 13.48 (0.0254/0.9660) | 13.48 | MATCH |
| rule-soft on page | 1.300 (0.6894/0.9115) | 1.30 | MATCH |
| ink-muted on panel | 6.009 (0.1191/0.9660) | 6.01 | MATCH |

Guards intact: `DesignTokensTest.php:887-914` (floor 1.05 at `:903`; boundary `:894-899`); G-18
thresholds `:752-754`; file last touched pre-audit. No new hardcoded grey hex outside `app.css`.

Visual holds: `page #F4F4FF` / `panel #FBFBFD` (`:136-137`), ink `#322A2F`/`#000000` (`:142-143`),
`bc102252` still last on the light block. Desktop pairs recaptured after a post-audit manifest
rebuild (21:45): 7/8 byte-identical; `dashboard-app` differs 0.146% of pixels, confined to a
38x147px clip-edge sliver — element diff zero. Fresh set: `screenshots/phaseA-2026-11-14/`.

Errata (recorded here only): (1) **GP-08 refuted** — zoomed re-read shows the HFW rail white/near-white
with dark nav text; `shell.css:179` paints it `var(--panel)`, the page declares no `data-theme`, and
the fresh capture is byte-identical: the audit's "dark-slate sidebar" was a full-frame vision misread
(affects 04's GP-08 block, 02's wizard row, 00's limitation 3). (2) **03 line-cites drifted** — dark
ink is `:490-493` (03 says `:486-489`), eva01 `:627-630` (03 says `:623-626`); values were and are correct.

Limitations: browser contrast gate not re-executed (pest-plugin-browser temp-path constraint; the
figures it enforces recomputed arithmetically); 6 of 16 figures spot-checked, not all; mobile pairs
not recaptured (tokens unmoved, desktop set byte-identical); GP-07's "Confirmed" basis remains
vision-model reading — flagged per brief, not upgraded.
