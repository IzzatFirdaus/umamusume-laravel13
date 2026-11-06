# Flows — Preferences

Date: 2026-11-06. HEAD: `67a4830`. Documentation only; no code changed.
Evidence: `app/Http/Controllers/PreferenceController.php`, `app/Http/Requests/UpdatePreferenceRequest.php`, `app/Models/Preference.php`, `app/Actions/BackupDatabase.php`, `resources/js/pages/Preferences/Edit.vue`.

---

## 1. Routes

| Method | URI | Name | Action |
| --- | --- | --- | --- |
| GET | `/preferences` | `preferences.edit` | `edit` |
| PUT | `/preferences` | `preferences.update` | `update` |
| GET | `/preferences/export` | `preferences.export` | `export` |
| POST | `/preferences/backup` | `preferences.backup` | `backup` |

One PUT covers every key because writing a preference is one action on the store, not one action per key.

---

## 2. Storage shape

`Preference` is a key/value table with three keys (`Preference::KEYS`):

| Key | Storage |
| --- | --- |
| `theme` | `light`, `dark`, `eva01`, or absent (follow the OS) |
| `failure_estimate` | `off` / `on` |
| `settings` | one JSON blob, sparse |

`SETTINGS_KEYS = ['default_scenario', 'recommendation_aggressiveness', 'stat_target_defaults', 'language', 'dashboard_layout', 'dashboard_hidden_cards']`.

Vocabularies: `AGGRESSIVENESS = ['conservative', 'balanced', 'aggressive']`, `LANGUAGES = ['en']`.

Absence is a real value: a null key deletes the row rather than storing a null, which is why "follow the OS" is the absence of a theme, not a fourth theme word.

---

## 3. Reading (`edit`)

Props: `theme` (null when absent or unrecognised), `settings` with each known key passed through a guard:

- `default_scenario` must be a `config('scenarios.scenarios')` key.
- `recommendation_aggressiveness` must be in `AGGRESSIVENESS`.
- `stat_target_defaults` survives only as integers.
- `language` must be in `LANGUAGES`.
- `dashboard_layout` normalises legacy words: `compact`/`focused` → `compact`, `wide`/`wide_bento` → `wide`, everything else → `standard`.
- `dashboard_hidden_cards` must be an array drawn from `active_career`, `next_best_action`, `quick_actions`, `recent_veterans`, `data_status`.

A hand-edited blob cannot reach the page as a half-number the form would then render as zero — the invented value these guards refuse.

Screen sections (`Edit.vue`): Layout width, Target defaults, Data, Theme, Language, Dashboard — each a `section.card` with `aria-labelledby` and the radiogroups carrying `role="radiogroup"`.

---

## 4. Writing (`update`)

`UpdatePreferenceRequest` requires `theme` and `failure_estimate` to be **present** (nullable), so a partial submit cannot silently drop a key:

- `theme` in `light|dark|eva01` or null.
- `failure_estimate` in `off|on`.
- `settings` present, nullable, array.
- `settings.default_scenario` must exist in config; `settings.recommendation_aggressiveness` in `AGGRESSIVENESS`; `settings.stat_target_defaults.*` integers `0..ScenarioCaps::hardCap()`; `settings.language` in `LANGUAGES`; `settings.dashboard_layout` in the six accepted words; `settings.dashboard_hidden_cards.*` in the five card keys.

`update()`:

1. Reads the previous theme **before** the write, so the flash can say whether the theme actually changed.
2. `theme` null deletes the row; otherwise `Preference::put('theme', ...)`.
3. `failure_estimate` always written.
4. `settings` filtered to drop nulls (an unset preference, not a stored null), with `stat_target_defaults` filtered one level deeper.
5. `dashboard_layout` canonicalised to `compact` / `standard` / `wide`; a `wide` that reaches the writer while `config('dashboard.wide_bento_enabled')` is off is stored as `standard`, never as a layout the Trainer cannot pick.
6. An empty blob deletes the row; otherwise `putSettings()`.
7. Redirects **back** — the control sits in the shared nav, so a save must not move the Trainer off the page they were working on. Flash: `Preferences saved.` or `Preferences saved. The theme change shows on your next reload.`

---

## 5. Export

`GET /preferences/export` returns a streamed JSON download named `trainer-desk-export-{Ymd-His}.json`.

Four keys, and only four — `PreferenceDataActionsTest` pins them:

```
{"training_runs":[...],"turn_entries":[...],"veterans":[...],"preferences":{"settings":...}}
```

Each table is echoed row by row through a `cursor()`, so memory is proportional to one row rather than to a whole career. A row that fails `json_encode` throws (`JSON_THROW_ON_ERROR`) rather than being emitted as nothing: a truncated export is a worse defect than a loud one.

---

## 6. Backup

`POST /preferences/backup` calls `BackupDatabase::to()`. The mechanism is SQLite's `VACUUM INTO`, which takes a consistent snapshot of a live WAL database. The file lands under `storage/app/backups/` and the flash carries its path, because the thing a Trainer needs to know about a server-side backup is where it went.

A failure is unhandled on purpose: there is no second feedback channel to invent, and the app's own 500 document is the error state the rest of the tool uses.

There is also a CLI door: `php artisan uma:backup {destination?}` writes to `storage/app/backups/uma-backup-{timestamp}.sqlite` by default.

---

## 7. Deliberate absences

| Absent | Why |
| --- | --- |
| Restore | Destructive and owner-scoped (`AGENTS.md` §5) |
| Reset | Destructive and owner-scoped |
| Theme for a fourth palette | Unrequested; the three words are the shipped set |
| Per-key endpoints | One PUT is one action on the store |
| A "rating" or scoring preference | No source records such a value |

The theme is also not a rail-collapse setting: the rail's collapsed/expanded choice lives in `localStorage` (`trainer-desk.nav-rail`), not here.

---

## 8. Verification surface

| Claim | Pinned by |
| --- | --- |
| Read/write round trip, guards, layout canonicalisation | `PreferenceControlsTest` |
| Export key contract | `PreferenceDataActionsTest` |
| Backup action | `UmaBackupTest`, `DatabaseSafetyTest` |
| Theme resolution shared prop | `DashboardTest`, `DesignTokensTest` |
