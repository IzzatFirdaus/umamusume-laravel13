# Trainer Desk — Frontend Audit, 2026-09-28

Documentation-only screenshot audit of every user-facing page. No application
code, test, view, config, route, or dependency was modified by this audit; the
tree under `app/`, `resources/`, `config/`, `tests/`, `routes/`, and `database/`
is untouched by this branch's commit (proof in §7).

---

## 1. Environment header

| Item | Value |
|---|---|
| Branch | `docs/frontend-review` (based on `master` @ `7d4b8cf`) |
| Boot commands | `New-Item -ItemType Directory .scratch-uma` → `$env:DB_DATABASE="D:\Projects\umamusume-laravel13\.scratch-uma\frontend-review.sqlite"` → `php artisan migrate --seed` → `php .scratch-uma\frontend-review-fixture.php` (via tinker `require`) → `.scratch-uma\serve-8144.cmd` (sets `DB_DATABASE`, then `php artisan serve --host=127.0.0.1 --port=8144`) |
| Scratch DB | `.scratch-uma/frontend-review.sqlite` (gitignored via `.gitignore:88 /.scratch-uma/`; the shared `database/database.sqlite` was never written — verified read-only) |
| Theme control | `Preference::put('theme', 'light'/'dark')` on the scratch DB; the layout renders `data-theme` server-side from `AppServiceProvider`'s view composer |
| Server PID | 20036 (`php-cgi.exe` child of the 8144 wrapper; `netstat`-verified bound to 127.0.0.1:8144 and serving the scratch DB) |
| Timestamp | 2026-09-28, captures 21:22–22:58 local (all taken after the scratch DB, the final view-code state, and the server-DB confirmation were in place) |
| Viewport | 1280×800, full-page captures, Playwright MCP |
| Capture format | `docs/frontend-review/2026-09-28/{page-slug}-{state}-{theme}.png`, each with `.console.txt` and `.network.txt` sidecars |

**Caveat recorded honestly:** a stale `php -S` listener from a prior session briefly
occupied port 8144 early in setup and served the old slice-5 scratch DB. The
audited server was confirmed (API listing + run-6 probe + R17 markers in the
rendered HTML) before every capture in this folder; the few light captures taken
during the ambiguous window were re-taken afterwards, so the 42 PNGs here all
reflect the confirmed scratch build.

### R17 fixture (seven states)

Built by `.scratch-uma/frontend-review-fixture.php`, seeded on top of
`php artisan migrate --seed` (24 tables, 10 skills, 2 Umamusume, 4 scenario defs):

1. **Run 1 — populated URA Finale:** 2 turns (energy/mood/fans), 4 skills across Suggested/Acquired/Skipped, 4 calendar slots (fan gate, mandatory goal, maiden gate, past goal) with one completed race (1st) and one Skipped entry.
2. **Run 2 — populated Unity Cup:** 1 logged turn; the team-rank/spirit-burst widgets render per config (`widgets: team_rank, bursts`).
3. **Run 3 — populated Trackblazer:** 1 turn, one priced G1 win plus one free-form win, both left unassigned → the meter's "no period reported" state.
4. **Run 4 — no scenario:** goal panels absent (D-220), baseline strip only.
5. **Run 5 — unpriceable entry (KI-10):** period 2 reported with a 3rd-place win no sourced table prices → "not yet totalled" state.
6. **Run 6 — empty run:** zero turns, first-turn guided-rail state.
7. **Review queue:** one pending `MatchCandidate` ("Vodka", Fuzzy, gametora-characters).

---

## 2. Route table

42 page-captures + 7 body captures (4 API, 2 exports, 1 health page). Every
visited route, its HTTP status, and its screenshot(s):

| Route | Status | Captures (light / dark) |
|---|---|---|
| `/` landing | 200 | `landing-default-light` (splash is theme-static; dark adds nothing — see F-1) |
| `/training-runs` populated | 200 | `runs-index-populated-light`, `runs-index-populated-dark` |
| `/training-runs` empty (0 runs, via scratch backup/restore) | 200 | `runs-index-empty-light` |
| `/training-runs/create` | 200 | `run-create-form-light`, `run-create-form-dark` |
| `/training-runs/create` validation error (server-side) | 200 (redirect back with errors) | `run-create-form-validation-error-light` |
| `/training-runs/1` URA Finale | 200 | `run-detail-ura-finale-light`, `run-detail-ura-finale-dark` |
| `/training-runs/2` Unity Cup | 200 | `run-detail-unity-cup-light`, `run-detail-unity-cup-dark` |
| `/training-runs/3` Trackblazer, no period reported | 200 | `run-detail-trackblazer-light`, `run-detail-trackblazer-dark` |
| `/training-runs/4` no scenario | 200 | `run-detail-no-scenario-light`, `run-detail-no-scenario-dark` |
| `/training-runs/5` KI-10 unpriceable ("not yet totalled") | 200 | `run-detail-unpriceable-light`, `run-detail-unpriceable-dark` |
| `/training-runs/6` empty first-turn | 200 | `run-detail-empty-first-turn-light`, `run-detail-empty-first-turn-dark` |
| `/training-runs/1/turns` POST preview (refresh/partial state) | 200 (POST render) | `run-detail-guided-preview-light`, `run-detail-guided-preview-dark` |
| `/training-runs/99` 404 | 404 | `error-404-run-light` |
| `/training-runs/{1..5}/export/csv` | 200 | `export-csv-run1.txt` (body capture, per spec for non-HTML) |
| `/training-runs/1/export/json` | 200 | `export-json-run1.txt` |
| `/training-runs/1/export/xlsx` | 404 (by design, PRD FR-C-5) | body embedded in F-9 |
| `/umamusume` populated (2 records, paginated) | 200 | `catalog-index-populated-light`, `catalog-index-populated-dark` |
| `/umamusume?search=special` filtered | 200 | `catalog-index-search-light`, `catalog-index-search-dark` |
| `/umamusume?search=zzzznone` empty result | 200 | `catalog-index-empty-light`, `catalog-index-empty-dark` |
| `/umamusume?status=GlobalAnnounced` | 200 | `catalog-index-filter-announced-light`, `catalog-index-filter-announced-dark` |
| `/umamusume?status=GlobalReleased` | 200 | `catalog-index-filter-global-light` |
| `/umamusume?page=2` out-of-range page | 200 | `catalog-index-page2-out-of-range-light`, `catalog-index-page2-out-of-range-dark` |
| `/umamusume/special-week` detail | 200 | `catalog-detail-populated-light`, `catalog-detail-populated-dark` |
| `/umamusume/nope-404` 404 | 404 | `error-404-catalog-light` |
| `/review` populated | 200 | `review-queue-populated-light`, `review-queue-populated-dark` |
| `/review` empty (via scratch backup/restore) | 200 | `review-queue-empty-light`, `review-queue-empty-dark` |
| `/design-preview` deprecated review surface | 200 | `design-preview-all-light`, `design-preview-all-dark` (forced via its own localStorage, see F-2) |
| `/up` framework health page (not in `routes/web.php`) | 200 | `up-health-page.txt` (body evidence for F-19: off-origin CDN loads at page time) |
| `/api/v1/umamusume` | 200 | `api-umamusume-index.txt` |
| `/api/v1/umamusume/special-week` | 200 | `api-umamusume-detail.txt` |
| `/api/v1/training-runs` | 200 | `api-training-runs-index.txt` |
| `/api/v1/training-runs/1` | 200 | `api-training-runs-detail.txt` |

**Captures: 42 screenshots, 42 console sidecars, 42 network sidecars, 6 response-body `.txt` files (4 API + CSV + JSON).**

Routes in `routes/web.php` with no GET surface (write-only, not screenable):
`runs.store` (POST), `runs.update` (PUT), `runs.destroy` (DELETE),
`runs.turns.update` (PUT), `runs.turns.destroy` (DELETE), `runs.skills.sync`
(POST), `review.resolve` (POST) — exercised indirectly through the preview and
validation captures; direct GET is a 405, recorded as unreachable in §8.

---

## 3. Per-page analysis

### 3.1 Landing `/` — `landing-default-light.png`

HTTP 200. **The page is the stock Laravel skeleton splash**: title "Laravel",
"Let's get started", "Deploy now" button linking `https://cloud.laravel.com`,
links to `laravel.com/docs`, `laracasts.com`, and the framework changelog, plus
~38 KB of vendored inline CSS. It ignores the stored theme preference (does not
use `components.layout`; no `data-theme` attribute renders). Off-origin links
exist in markup but generate **zero page-load requests** (sidecars show
all-same-origin network). This is the KI-3 "residual" the KI record itself
flags: the bunny font links are gone, but the page is still not a product
surface. For a local-only trainer tool, "Deploy now" (C-4 scope, PRD §6
non-goals) is the sharpest copy failure in the build.

### 3.2 Training runs index — `runs-index-populated-{light,dark}.png`, `runs-index-empty-light.png`

HTTP 200. Six fixture runs listed as cards ("Special Week · URA Finale" etc.).
Both themes render correctly from tokens; empty state ("No runs yet / A run
holds the turns you log…") exists and is honest (D-220: absence is stated, not
zeroed). No pagination (FR-6.1 cut) — 6 rows render in one page. Contrast probe
light+dark: 0 WCAG failures; no off-origin requests; the only console line is
the Laravel Boost dev-time logger (tooling artifact, not shipped).

### 3.3 Create form — `run-create-form-{light,dark}.png`, `run-create-form-validation-error-light.png`

HTTP 200. Three selects (Umamusume, Scenario optional, Status) + notes. Native
`required` blocks empty submit client-side; bypassing it (via `form.submit()`)
lands the server-side error: **"The umamusume id field is required."** — raw
snake_case field name surfaced to the Trainer instead of the form's own label
"Umamusume". Error text renders `text-risk` and is linked into the select's
accessible name. Validation error state exists and re-populates — this is real
coverage, only the message vocabulary drifts (F-5).

### 3.4 Run detail — the six fixture states

All three themes render the token shell. Shared observations:

- **Header** "Special Week Active · URA Finale" + CSV/JSON export links.
- **Resource strip** (turn/energy/fans + scenario widgets from config) and
  **stat band** with derived grade letters, halved-gains note at 1,200, and the
  `[Provisional]` grade-scale disclosure — all present in both themes.
- **Race calendar** renders 24 slots with per-state labels; the maiden-gated
  slot ("Naruta Kinpa Cup") renders "Run, Naruta Kinpa Cup" in the calendar
  legend for run 1 because the fixture logged a completed entry — state mix
  works.

Per-state:

- **Run 1 URA Finale** (`run-detail-ura-finale-*`): calendar fan gate (15,000
  fans), mandatory goal, completed race cell, Skipped cell, skills in all three
  acquisition groups, 2-row turn table with mood pills (`NORMAL →`, `GREAT ↑`).
- **Run 2 Unity Cup** (`run-detail-unity-cup-*`): Team Rank and Spirit Bursts
  widgets render with "N/A / not yet recorded". **There is no capture path for
  them** — no `team_rank`/`bursts` column exists on `turn_entries` (verified via
  `Schema::getColumnListing`) and the guided form posts no such field, so these
  two widgets are permanently N/A on every run screen (F-6).
- **Run 3 Trackblazer** (`run-detail-trackblazer-*`): "no period reported" meter
  state. Copy bug: **"2 logged results have no period entered against it"** —
  plural subject, singular pronoun (`grade-point-meter.blade.php:105-106` names
  the verb but not the pronoun).
- **Run 4 no scenario** (`run-detail-no-scenario-*`): goal panels correctly
  absent; the h1 says "No scenario set" — but the resource strip's Turn cell
  caption and the guided rail's chip both print **"URA Finale"**, the config
  baseline default, for a run with scenario `null` (F-3, a truthfulness bug by
  the project's own D-220/D-256 standards).
- **Run 5 KI-10 unpriceable** (`run-detail-unpriceable-*`): "not yet totalled: 1
  logged result has no published Grade Point value, so any total here would
  count less than this run earned" renders — the KI-12 R18 copy landed. Note
  the strip still reads "Grade Points N/A / not yet recorded", contradicting the
  panel's precise wording two sections below (F-4).
- **Run 6 empty** (`run-detail-empty-first-turn-*`): first-turn rail state with
  the "This is the run's first turn…" sentence and all-withheld "N/A" values.
  Honest.
- **Preview/partial** (`run-detail-guided-preview-*`): the "Preview this turn"
  submit renders the step-2 preview panel — but the address bar shows
  `/training-runs/1/turns` (the POST endpoint re-renders the view instead of
  redirecting with flash input). **A browser refresh of the preview state
  returns 405.** Every other write flow in the app uses PRG; this one doesn't
  (F-7).

### 3.5 Catalog index + variants — `catalog-index-*.png`

HTTP 200. Search + status filter + pagination links (2 per page, "1, 2" with a
next arrow). States covered: populated, name-filtered, no-match empty ("No
Umamusume match…" — the same sentence serves zero-result and out-of-range-page),
and `?page=2` **out-of-range renders the generic empty message instead of a
distinct "that page is past the end" state** (F-8). Filter chips show the raw
enum key (`?status=GlobalAnnounced` keeps the query string) but labels render
through `->label()`, so the visible text is compliant. Both themes clean
(0 contrast failures).

### 3.6 Catalog detail — `catalog-detail-populated-{light,dark}.png`

HTTP 200 for `/umamusume/special-week`. Name, Japanese name, release status,
aliases, and an "All runs" section. Tokai Teio renders with **no runs** (all
fixture runs attach to Special Week) — the "none yet" path is therefore
covered in the same light/dark pair as the populated one (populated half).
All fields render from tokens; no off-origin requests.

### 3.7 Review queue — `review-queue-{populated,empty}-{light,dark}.png`

HTTP 200. Populated: "Vodka / ヴォーカ / Fuzzy · gametora-characters ·
2026-09-28" with the verdict select, optional id, alias-language select, and
"Resolve Vodka" button (PRG confirmed in `ReviewController::resolve` → redirect
with flash). Empty state exists ("nothing to review"). The resolve buttons carry
accessible names. Both themes clean.

### 3.8 404s — `error-404-catalog-light.png`, `error-404-run-light.png`

HTTP 404 on both. **Bare Laravel error page**: a small centered "404 / Not
Found" with no app chrome, no navigation, no theme, and no route back into the
product. The console sidecars record the expected resource error. Light-only per
protocol (the page has no theme sensitivity to measure). This is the single
biggest state-coverage gap in the build (F-10).

### 3.9 Deprecated `design-preview` — `design-preview-all-{light,dark}.png`

HTTP 200. Renders all four scenario compositions from `config/scenarios.php`
with real components. Three findings:

- Its inline script **defaults to dark on empty `localStorage` and ignores the
  server-rendered `Preference` theme entirely** — the page's banner is static
  `data-theme="light"` in markup, overridden pre-paint by the script. It writes
  `uma-theme` to localStorage, the exact client-persistence pattern ADR-0006 /
  D-104 forbids for the app shell (F-2). The dark capture was taken by forcing
  `localStorage.uma-theme=dark`.
- The `?step=` param only relights the step chips; all four scenario sections
  always render, so `?step=outcome` and `?step=skill` are byte-identical page
  states beyond the chip row.
- It is the route the header comment says to delete when the real run screen
  landed; the real run screen now exists (§3.4), so **the whole surface is now
  deletion-debt** — KI-8's 500 is fixed (the page renders), but the route
  outlives its purpose (F-15).

### 3.10 Exports and API bodies

- **CSV** (`export-csv-run1.txt`): header `turn,speed,stamina,power,guts,wit,sp,condition`
  — the logged `energy`, `fans`, and `mood` columns are **not exported**, and
  `condition` (only writable via the collapsed raw form, always empty on guided
  runs) is (F-9).
- **JSON** (`export-json-run1.txt`): same omission through `TurnEntryResource`
  (exposes only `id, turn, five stats, sp, condition`), with correctly
  `JSON_UNESCAPED_UNICODE`-encoded Japanese names (raw bytes verified —
  `スペシャルウィーク` intact).
- **XLSX**: 404 by design (PRD FR-C-5) — not a defect, recorded for completeness.
- **API** (`api-*.txt`): all four endpoints return 200 with `data` envelopes;
  the runs list carries the same `turns[].condition` shape as JSON export.

---

## 4. Categorized findings

Severity: **N** = needs fix before merge, **O** = observation, **D** = drift from
design record. Counts below.

### Token/Theme drift

- **F-1 (N)** — `/` landing ignores stored theme (no `data-theme`); hard-coded
  `bg-white`, `dark:bg-[#0a0a0a]`, and ~20 literal hex values live only in
  `welcome.blade.php` (grep-verified: every other view uses tokens). KI-3's
  residual, now measurable: the splash cannot be dark-mode audited at all.
- **F-2 (N)** — `design-preview.blade.php:12-27` pre-paint script reads/writes
  `localStorage.uma-theme` and defaults dark, bypassing the `Preference` table —
  directly contrary to ADR-0006/D-104, and the page's static markup ships
  `data-theme="light"` that the script then overwrites (flash-prone by
  construction for the app's own mechanism).
- **F-4 (O/D)** — run-5 strip "Grade Points — N/A / not yet recorded" vs. panel
  "not yet totalled" one section below: same datum, two vocabularies, on one
  screen.
- **F-15 (O)** — design-preview is the last surface outside the token/layout
  system (its own inline theme control); its own header says to delete it now
  the real run screen exists.

### State-coverage gaps

- **F-10 (N)** — 404 pages are bare framework output: no branded layout, no
  navigation, no "back to runs" action. Every mistyped slug dead-ends outside
  the product. (Light-only captures; the page is theme-static.)
- **F-9 (N)** — CSV and JSON exports ship only `turn, speed, stamina, power,
  guts, wit, sp, condition`. `energy`, `fans`, and `mood` are captured by the
  guided form and shown on the screen but **absent from both exports**, while
  `condition` — reachable only through the collapsed raw-entry form (D-53) and
  empty on every guided turn — is present and always blank. The export shape
  and the logged/displayed data disagree (TrainingRunController::export +
  TurnEntryResource).
- **F-8 (O)** — `?page=N` beyond the last page reuses the zero-result empty
  sentence ("No Umamusume match") instead of a distinct past-the-end state.
- **F-7 (N)** — guided "Preview this turn" renders the POST endpoint's URL into
  the address bar (no PRG): refresh → **405 Method Not Allowed**. The only
  write flow in the app that is not refresh-safe.
- **F-6 (O)** — Unity Cup's Team Rank / Spirit Bursts strip widgets have no
  input path and no column: their "populated" state is unreachable on the run
  screen (only the deprecated preview fakes values for them).
- **F-11 (O)** — no `500`/error page state exists for anything but framework
  exceptions; no custom `errors/` views ship (`resources/views/errors/` absent).
- **F-19 (N)** — the framework's `/up` health page (Laravel's built-in, not in
  `routes/web.php`) is reachable at 127.0.0.1:8144/up and loads **remote assets
  at page time**: `fonts.bunny.net` (Figtree stylesheet) and
  `cdn.jsdelivr.net/npm/@tailwindcss/browser@4`. For a local-only tool (PRD
  NFR-1 / CONSTRAINTS C-4) that is a live off-origin dependency on an
  indexable route — the exact pattern KI-3 closed for `welcome.blade.php`
  still lives here. No screenshot per protocol (not a product route); body
  evidence is in the §2 route table row.

### Accessibility

- **F-12 (O)** — the run-detail skills editor wraps three controls in one
  `<label>` (`runs/show.blade.php:395-407`), so only the first gets an
  implicit name: the "Skill" combobox is named, the adjacent acquisition-status
  `<select>` (Suggested/Acquired/Skipped) is **unnamed** in the accessibility
  tree, and the turn `<input type=number>` relies on `placeholder="Turn"`
  alone — which vanishes once the field holds a value. A screen-reader user
  hears one named control, one unnamed combobox, and an unlabeled number.
  Contrast: the create form labels every field, so the gap is local to this
  block.
- **F-13 (O)** — the hidden radio inputs (`class="peer size-px opacity-0"`) are
  correctly named in the a11y tree and carry `peer-focus-visible` rings
  (design-record compliant, verified in `guided-step.blade.php:126-131`); no
  unnamed interactive controls found anywhere (`/review` "Resolve" button and
  catalog search selects all carry accessible names — verified per-page).
- Positive: skip-link present on every layout page; `aria-label`ed regions on
  the run screen; energy bars expose "Energy 74 of 100" accessible names.

### Copy/Lore

- **F-3 (N)** — run 4 (scenario `null`): h1 "No scenario set" contradicted by
  the resource strip caption "URA Finale" and the guided-rail chip "URA Finale"
  (`resource-strip.blade.php:62`, fed from `$scenario ?? 'ura_finale'` default).
  A claim about the run the data does not support (D-256-adjacent).
- **F-5 (O)** — validation error "The umamusume id field is required." leaks the
  DB-ish field name where the form's own label says "Umamusume".
- **F-14 (O)** — design-preview calendar cells double-announce: img alt "Feb
  Late: **Next, Next**" — the state name and label are the same string
  (routes/web.php preview data uses `'label' => 'Next'` for a state the legend
  also names; preview-surface only).
- **F-18 (O)** — grade-point-meter copy, plural case: "**2 logged results have
  no period entered against it**" — the verb agrees (`results have`) but the
  pronoun stays singular (`against it`); `grade-point-meter.blade.php:104-106`.
  Reproduced live on run 3 (light+dark captures).
- Lore grep (banned equine terms, `🏇`) over `resources/views/**`: **zero
  matches**. All fixture seed strings and audit copy here were screened.
  ("Homestretch Haste", "Unstoppable" etc. are verbatim source skill names —
  allowed on the data path, gated at display; no authored copy uses equine
  framing.)
- KI-7 (em dashes in rendered Blade copy): **not reproduced** — no em dash
  renders in any captured page.

### Layout (1280px)

- No horizontal overflow, clipped controls, or wrapped-header regressions found
  at 1280×800 in either theme across all 42 captures (full-page review of the
  DOM snapshots taken per capture + visual scan of the PNGs). The three-column
  run layout (strip rail / log / actions) and the 12×2 calendar grid are the
  widest surfaces and both fit with room.
- **F-16 (O)** — the run-detail page is extremely long (the fixture run 1
  captures at ~8× the viewport height); the sticky strip (`lg:sticky`) works at
  1280, but nothing summarizes; not a regression, recorded as drift risk.

### Contrast (N1-class, D-288 gate)

Programmatic WCAG AA probe (text vs. computed stacked background, 4.5:1 /
3:1-large) on every theme-sensitive capture:

- **0 failures** in light and dark on: runs index, run 1–6, guided preview,
  create form (+error), catalog index (+search/empty/filter variants), catalog
  detail, review queue (both states). The `--color-ring` token is separate from
  the up/down tokens precisely to keep 1.4.11 safe (app.css:73-94); KI-9's
  selection-gold fix is verified holding (no 1.59:1 pairs found).
- **F-17 (N1-pass)** — the probe's floor cases (faint ink on sunken, `green-tint`
  band word) measured 6.4:1+ in both themes, matching slice-5 §3's recorded
  numbers. **No N1-class gate failures found in this build.**

### Count: 19 numbered observations (F-1…F-19) — 7 need-fix (N: F-1, F-2, F-3, F-7, F-9, F-10, F-19), 11 minor/observation (O), and 1 N1-class gate result recorded as a pass (F-17: zero contrast failures, so no D-288 gate finding was raised).

---

## 5. Cross-reference: known issues vs. visibility in this build

| KI | Subject | Visible here? |
|---|---|---|
| KI-1 | `x-layout` Vite entry missing — RESOLVED | Not visible. Every page loads `/build/assets/app-*.{css,js}` 200. |
| KI-2 | Catalog cache kills models — RESOLVED | Not visible. Catalog index/detail/search all 200, no 500s. |
| KI-3 | welcome breaks offline — RESOLVED (font links), residual open | **Partially visible.** Zero off-origin *requests* (fixed); but the splash itself persists (F-1, F-15-adjacent) — the "residual, not fixed" note is confirmed live. |
| KI-4 | `make lore` blind to untracked — RESOLVED | n/a to rendered UI. |
| KI-5 | fabricated skill name — FIXED | Not visible. Banned-pattern grep clean (§4 Copy/Lore). |
| KI-6 | shipped font — CLOSED as decision | Not visible. System stack renders per DESIGN.md §2.2. |
| KI-7 | em dashes in Blade copy — RESOLVED | Not visible. No em dash renders in any capture. |
| KI-8 | design-preview 500s on grade badge — RESOLVED | Not visible. `/design-preview` returns 200 in both themes (grade letters render). |
| KI-9 | selection gold 1.59:1 light — RESOLVED | Not visible. Contrast probe found no sub-3:1 selection pairs (F-17). |
| KI-10 | Grade Points unpriceable — schema half CLOSED, ratio half OPEN | **Visible as designed.** Run 5 renders the honest "not yet totalled" withholding (the OPEN half is exactly what F-4/F-16 observe at the copy boundary). Fixture reproduces it. |
| KI-11 | seeder stub + green-tint debt — BOTH CLOSED | Not visible. Slots seeded via fixture; green-tint band word measures (F-17). |
| KI-12 | meter says "nothing entered" — RESOLVED (R18) | **Visible as fixed.** Run 5 renders the three-state copy; run 3 renders the "no period reported" state. The plural/pronoun bug (F-18) is new, filed in §4. |
| KI-13 | models exist on no ref — RESOLVED | Not visible. Everything resolves on this branch. |
| KI-14 | rail declared fake radios — RESOLVED | Not visible. `role=radio` semantics present with focus rings (F-13 positive). |
| KI-15 | GP track selection unsourced — OPEN (×2 duplicate sections) | **Visible as designed.** Run 3/5 meters show the `standard` track (60/300/300) with the record's own caveat rendered beside it. Also: `KNOWN-ISSUES.md` carries KI-15 **twice** (lines 645 and 678, byte-near-identical) — a doc-integrity observation for the file's owner, not a UI defect. |

**No previously-resolved KI regressed in this build.** KI-10 and KI-15 remain
open and both are *correctly* visible (they are deliberate withholdings).

---

## 6. Unreachable routes

| Route | Reason |
|---|---|
| `runs.update` PUT `/training-runs/{run}` | Write-only verb — GET is 405 by design; exercised through the scenario-change form (no visible defect). |
| `runs.destroy` DELETE | Write-only; its UI trigger (the "Delete run" button) was deliberately **not** activated (destructive; documentation-only audit). |
| `runs.turns.update` / `runs.turns.destroy` | Write-only verbs on turn rows; no screenable GET surface. |
| `runs.skills.sync` POST | Write-only. |
| `review.resolve` POST | Write-only; deliberately not activated (mutates the candidate row; PRG confirmed from code + the success path is covered by the empty-queue capture). |
| `runs.export` `xlsx` | 404 is the implementation (PRD FR-C-5 cut Excel): not an error, a designed absence. |

---

## 7. Tree integrity (audit changed no source)

- `git check-ignore -v .scratch-uma/` → `.gitignore:88:/.scratch-uma/	.scratch-uma/`
- The audit commit (`docs(frontend-review): screenshot audit…`) contains **only**
  `docs/frontend-review/**`: `git show --name-only HEAD` lists zero files under
  `app/`, `resources/`, `config/`, `tests/`, `routes/`, or `database/`.
- The branch base moved mid-audit: the concurrent Slice-7/Slice-8 line committed
  schema, model, and view changes (`team-rank-gauge`, `race-panel`, period
  columns, ~1,207 insertions across 17 source files) onto this branch's history
  between `7d4b8cf` and the audit commit. Those commits are **not** the audit's;
  they are recorded here because every PNG post-dates the view code they landed
  (capture times 21:22–22:58 local vs. final view mtime 20:37 local, §1 caveat).
- The shared `database/database.sqlite` was never opened for write by this
  audit: all migrate/seed/tinker/screenshot commands ran with `DB_DATABASE`
  pointed at the gitignored `.scratch-uma/frontend-review.sqlite` (probe
  `php .scratch-uma/list-runs.php` without the env var shows the shared DB
  still holds its own single run, untouched).
