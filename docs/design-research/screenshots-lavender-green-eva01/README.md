# Frontend Screenshots

This directory contains automated screenshots of all frontend pages captured from the Trainer Desk application running at `http://localhost:8000`.

## Capture Script

The screenshots are generated using `tools/capture-screenshots.mjs` which uses Playwright to:

1. Navigate to each route defined in `routes/web.php`
2. Capture screenshots at multiple viewport sizes:
   - **mobile** (390×844) - iPhone 12/13/14 Pro
   - **tablet** (768×1024) - iPad portrait
   - **desktop** (1280×720) - Standard desktop
   - **desktop-wide** (1920×1080) - Wide desktop

## Running the Capture

### Option 1: With automatic server management (recommended)
```bash
./tools/capture-screenshots.sh
```

This starts `php artisan serve --port=8000`, runs the capture, then stops the server.

### Option 2: Manual server
```bash
# Terminal 1: Start server
php artisan serve --port=8000

# Terminal 2: Run capture
node tools/capture-screenshots.mjs
```

## File Naming Convention

Screenshots follow the pattern: `{route-name}-{viewport}.png`

Examples:
- `home-mobile.png` - Dashboard at mobile viewport
- `career.scenario-desktop.png` - Career scenario selection at desktop
- `runs.cockpit-tablet.png` - Career cockpit at tablet viewport
- `catalog.index-desktop-wide.png` - Trainee catalog at wide desktop

## Viewport Sizes

| Name | Width × Height | Device Profile |
|------|---------------|----------------|
| mobile | 390 × 844 | iPhone 12/13/14 Pro |
| tablet | 768 × 1024 | iPad portrait |
| desktop | 1280 × 720 | Standard laptop/desktop |
| desktop-wide | 1920 × 1080 | Full HD monitor |

## Routes Captured

The script captures all routes from `routes/web.php` including:

### Static Routes (no parameters)
- Dashboard (`/`)
- Career Setup Wizard (6 steps)
- Career Snapshot Entry (3 steps)
- Catalog Index pages (Trainees, Skills, Support Cards)
- Training Runs Index, Create, Import
- Review Queue
- Legacy Lab Index, Compare
- Veterans Library, Compare
- Preferences
- Database Hub (12 sub-pages)

### Dynamic Routes (require run ID)
A test run is automatically created to capture:
- Career Cockpit
- Race Decision / Planner
- Skills Planner
- Training Decision
- Inheritance Event
- Event Decision
- Career Timeline
- Career Result
- Deck Builder
- Save Veteran
- Legacy Builder

### Detail Routes (may 404 if data missing)
- Trainee Detail
- Skill Detail
- Support Card Detail
- Veteran Detail

## Output

After capture completes, a `capture-report.json` is generated with:
- Timestamp
- Base URL
- Viewports tested
- Total routes attempted
- Success/failed/skipped counts
- Detailed results per route/viewport

## Requirements

- Node.js 18+
- Playwright (`npm install` installs `@playwright/test`)
- PHP 8.3+ with Laravel
- SQLite database (seeded via `php artisan migrate --seed`)

## Integration with Design Research

These screenshots serve as:
- Visual regression baselines
- Design system documentation
- Cross-viewport layout verification
- Stakeholder review artifacts
- Historical design records

See `docs/design-research/` for related design documentation.