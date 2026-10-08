<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\BackupDatabase;
use App\Http\Requests\UpdatePreferenceRequest;
use App\Models\Preference;
use App\Services\ScenarioCaps;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The only writer for the `preferences` table (PRD US-11, SCREEN_SPEC.md §7-5).
 *
 * Two scalar keys, one row each, and one structured blob in a row of its own. The theme's third
 * authorized value, follow the OS, is stored as the absence of a row rather than as the word
 * `system`, because that is what the composer in `AppServiceProvider` already resolves; the
 * settings blob resolves "nothing stored" the same way, by dropping its row.
 *
 * The Data actions are the two the app can actually perform (SCREEN-024, the D18b slice plan):
 * Export streams this tool's own data as JSON, and Backup writes a server-side snapshot. Restore
 * and Reset are destructive and owner-scoped, so they are absences on the screen rather than
 * routes that would need a ruling this slice does not carry.
 */
class PreferenceController extends Controller
{
    public function edit(): Response
    {
        // The same rule the layout composer applies: a stored value outside the two resolved
        // themes is not a theme, so it must not come back preselected as if it were.
        $theme = Preference::get('theme');
        $settings = Preference::settings();

        return Inertia::render('Preferences/Edit', [
            'theme' => in_array($theme, ['light', 'dark'], true) ? $theme : null,
            'failureEstimate' => Preference::get('failure_estimate') ?? 'off',
            // D18 (SCREEN-024): Settings categories gained a Data panel and a Game-version panel.
            // `verified_at` is a config fact, not a literal, so the badge cannot drift from the
            // matrix it describes — mirrors DashboardController::dataStatus().
            'verifiedAt' => config('scenarios.verified_at'),
            // Import has a live route; the other Data actions are named absences on the page.
            'importUrl' => route('runs.import'),
            // D18b (SCREEN-024): the stored structured preferences, normalised so the page never
            // tests for a missing offset. `stat_target_defaults` carries the matrix's own stat
            // names as keys, exactly as the blob was validated to.
            'settings' => [
                'default_scenario' => isset($settings['default_scenario']) && is_string($settings['default_scenario'])
                    ? $settings['default_scenario']
                    : null,
                'recommendation_aggressiveness' => isset($settings['recommendation_aggressiveness']) && is_string($settings['recommendation_aggressiveness'])
                    ? $settings['recommendation_aggressiveness']
                    : null,
                'stat_target_defaults' => isset($settings['stat_target_defaults']) && is_array($settings['stat_target_defaults'])
                    ? $settings['stat_target_defaults']
                    : null,
                'language' => isset($settings['language']) && is_string($settings['language'])
                    ? $settings['language']
                    : null,
            ],
            // The Default-scenario control reads the matrix as a whole — one option per entry the
            // config composes — and never branches on one of them (D-240, gate G-33).
            'scenarioOptions' => collect((array) config('scenarios.scenarios'))
                ->map(static fn (mixed $definition, string $key): array => [
                    'value' => $key,
                    'label' => (string) ($definition['label'] ?? $key),
                ])
                ->values()
                ->all(),
            'statOrder' => array_values((array) config('scenarios.stat_order')),
            'hardCap' => ScenarioCaps::hardCap(),
            // The three values the preference carries, labelled here so the screen and the write
            // path read one list rather than two.
            'aggressivenessOptions' => array_map(
                static fn (string $value): array => ['value' => $value, 'label' => ucfirst($value)],
                Preference::AGGRESSIVENESS,
            ),
            'exportUrl' => route('preferences.export'),
            'backupUrl' => route('preferences.backup'),
        ]);
    }

    public function update(UpdatePreferenceRequest $request): RedirectResponse
    {
        $preferences = $request->validated();

        if ($preferences['theme'] === null) {
            Preference::query()->whereKey('theme')->delete();
        } else {
            Preference::put('theme', (string) $preferences['theme']);
        }

        Preference::put('failure_estimate', (string) $preferences['failure_estimate']);

        // The blob is stored whole and sparse: a key the form sent as null is an unset preference,
        // not a null to store — the model's own rule for absence, and the reason a saved-empty
        // blob drops the row the way the theme's follow-the-OS does.
        $settings = array_filter(
            (array) ($preferences['settings'] ?? []),
            static fn (mixed $value): bool => $value !== null,
        );

        if (isset($settings['stat_target_defaults']) && is_array($settings['stat_target_defaults'])) {
            $settings['stat_target_defaults'] = array_filter(
                $settings['stat_target_defaults'],
                static fn (mixed $value): bool => $value !== null,
            );
        }

        if ($settings === []) {
            Preference::query()->whereKey('settings')->delete();
        } else {
            Preference::putSettings($settings);
        }

        // Back to wherever the Trainer was: the control sits in the shared nav, so a save that
        // landed them on a preferences screen would move them off the page they were working on.
        return redirect()->back()->with('status', 'Preferences saved.');
    }

    /**
     * The Data panel's Export (SCREEN-024): this tool's own data as one JSON download.
     *
     * Streamed, and to no temporary file — each table is echoed row by row through a cursor, so a
     * career's size costs memory proportional to one row, not to the whole table. The four keys
     * are the export's contract and `PreferenceDataActionsTest` pins them; anything else the
     * export later carries is an addition to that test, not a free key.
     */
    public function export(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            echo '{';
            self::streamRows('training_runs', DB::table('training_runs')->orderBy('id')->cursor());
            echo ',';
            self::streamRows('turn_entries', DB::table('turn_entries')->orderBy('id')->cursor());
            echo ',';
            self::streamRows('veterans', DB::table('veterans')->orderBy('id')->cursor());
            // The structured preferences: the decoded blob, or null when none was ever saved.
            echo ',"preferences":{"settings":'.json_encode(Preference::settings() ?: null, JSON_THROW_ON_ERROR).'}';
            echo '}';
        }, 'trainer-desk-export-'.now()->format('Ymd-His').'.json', ['Content-Type' => 'application/json']);
    }

    /**
     * Streams one table's rows into the export as a JSON array, without holding the table.
     *
     * @param  iterable<int, mixed>  $rows
     */
    private static function streamRows(string $key, iterable $rows): void
    {
        echo '"'.$key.'":[';

        $first = true;

        foreach ($rows as $row) {
            echo $first ? '' : ',';
            // Throw rather than fall silent: a row that does not encode would otherwise be emitted
            // as nothing, and a truncated export is a worse defect than a loud one.
            echo json_encode((array) $row, JSON_THROW_ON_ERROR);
            $first = false;
        }

        echo ']';
    }

    /**
     * The Data panel's Backup (SCREEN-024): a server-side snapshot, never a download.
     *
     * The mechanism is `VACUUM INTO`, which holds a consistent snapshot of a live WAL database;
     * the file lands under `storage/app/backups/` and the flash carries its path, because the
     * thing a Trainer needs to know about a server-side backup is where it went. A failure is
     * unhandled on purpose: there is no second feedback channel to invent, and the app's own 500
     * document is the error state the rest of this tool already uses.
     */
    public function backup(): RedirectResponse
    {
        $destination = BackupDatabase::to();

        return redirect()->back()->with('status', "Backup written to {$destination}.");
    }
}
