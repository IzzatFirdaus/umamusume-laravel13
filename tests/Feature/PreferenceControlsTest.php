<?php

declare(strict_types=1);

use App\Models\Preference;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;
use App\Models\Veteran;
use App\Services\Career\SetupDraft;
use Inertia\Testing\AssertableInertia as Assert;
use Symfony\Component\HttpFoundation\StreamedResponse;

/*
 * SCREEN_SPEC.md §7-5 / PRD US-11: the `preferences` table and the server-side theme render both
 * shipped, and nothing in `resources/views/` could write to them, so the dark theme was reachable
 * only by inserting a row by hand (audit O-1: "the dark theme ships with no way to select it").
 *
 * The first-paint half of this story is already pinned in `DesignTokensTest:100-131` (a stored
 * theme renders into `<html data-theme>` and the pre-paint script is dropped), so this file does
 * not re-assert it. What it adds is the control: the two authorized keys, their allowed values,
 * the refusal of anything else, and what the off-by-default failure estimate is allowed to mean.
 *
 * `failure_estimate` is stored and read back, and it shows no number, because there is no number
 * to show: ADR-0001 §3 records that no source publishes a failure curve, and requires that a
 * numeric estimate render with its formula and parameters visible on the same surface. Wiring a
 * percentage here would be the fabricated statistic that section refuses, so the control says it
 * shows nothing yet and §7-5 records the block.
 */

it('offers both preferences with their default states on an unset database', function (): void {
    test()->get('/preferences')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Preferences/Edit')
            ->where('theme', null)
            ->where('failureEstimate', 'off'));

    // Absence is the default for both keys: no row means follow the OS, and the estimate is off
    // by default (PRD US-11). A page that rendered "light" as if it were stored would claim a
    // preference the Trainer never set.
    expect(Preference::count())->toBe(0);
});

it('is reachable from the shell navigation', function (): void {
    // The shell is client-rendered now (ADR-0020 §1). That its Settings link is reachable, and
    // that the shell carries no form, is asserted in tests/browser/preferences.spec.ts.
    test()->get('/umamusume')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Catalog/Index'));
});

it('writes the theme through the control and renders it on the next request', function (): void {
    test()->put('/preferences', ['theme' => 'dark', 'failure_estimate' => 'off', 'settings' => []])
        ->assertRedirect();

    expect(Preference::get('theme'))->toBe('dark');

    // First paint, not a script fixing it afterwards: the attribute is in the document the
    // server sent.
    test()->get('/umamusume')->assertOk()->assertSee('data-theme="dark"', false);
});

it('switches the theme back to following the system by dropping the row', function (): void {
    Preference::put('theme', 'light');

    test()->put('/preferences', ['theme' => '', 'failure_estimate' => 'off', 'settings' => []])->assertRedirect();

    // US-11 authorizes three values and the third is the absence of a row, so "follow the system"
    // has to remove the preference rather than store the word `system`, which the composer in
    // AppServiceProvider:45 would honour as no theme anyway while leaving a dead row behind.
    expect(Preference::get('theme'))->toBeNull()
        ->and(Preference::whereKey('theme')->exists())->toBeFalse();
});

it('persists the failure estimate both ways', function (string $value): void {
    test()->put('/preferences', ['theme' => 'light', 'failure_estimate' => $value, 'settings' => []])->assertRedirect();

    expect(Preference::get('failure_estimate'))->toBe($value);
})->with(['on', 'off']);

it('passes the stored failure estimate to the control', function (): void {
    // The checkbox is now client-rendered (Vue), so the server-side contract is the prop the
    // page reads; the rendered checked state is the component's, exercised in the browser.
    Preference::put('failure_estimate', 'off');

    test()->get('/preferences')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('failureEstimate', 'off'));

    Preference::put('failure_estimate', 'on');

    test()->get('/preferences')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('failureEstimate', 'on'));
});

it('refuses a theme value this tool does not store', function (): void {
    test()->put('/preferences', ['theme' => 'graphite', 'failure_estimate' => 'on', 'settings' => []])
        ->assertSessionHasErrors('theme');

    expect(Preference::get('theme'))->toBeNull();
});

it('refuses a failure-estimate value that is not the pair the key is authorized for', function (): void {
    test()->put('/preferences', ['theme' => 'light', 'failure_estimate' => 'sometimes', 'settings' => []])
        ->assertSessionHasErrors('failure_estimate');

    expect(Preference::get('failure_estimate'))->toBeNull();
});

it('refuses a preference key that is not one of the two US-11 authorizes', function (): void {
    test()->put('/preferences', ['theme' => 'light', 'failure_estimate' => 'on', 'settings' => [], 'display_timezone' => 'UTC'])
        ->assertSessionHasErrors('preferences');

    // US-11 states the key set is a contract, not a free-form blob, and the migration's own
    // docblock says a third key is a PRD change. Silently dropping it would let a form outlive
    // its ruling.
    expect(Preference::whereKey('display_timezone')->exists())->toBeFalse()
        ->and(Preference::pluck('key')->all())->toEqual([]);
});

it('stores preferences in the database and not in browser storage', function (): void {
    test()->put('/preferences', ['theme' => 'dark', 'failure_estimate' => 'on', 'settings' => []])->assertRedirect();

    $html = test()->get('/preferences')->assertOk()->getContent();

    // PRD §6 non-goal 12 cuts a second source of truth, so nothing here may be mirrored to
    // localStorage. The head script that reads the OS is the fallback for *no* stored theme and
    // must not become a preferences store.
    expect($html)->not->toContain('localStorage')
        ->not->toContain('uma-theme')
        ->and(Preference::whereKey('theme')->value('value'))->toBe('dark');
});

it('passes the verified-at date and the import route to the Data panel', function (): void {
    // D18 (SCREEN-024): the Data category's Import link and the Game-version panel's verified
    // date are sourced from config and a named route, not inventoried by this controller.
    test()->get('/preferences')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('verifiedAt', config('scenarios.verified_at'))
            ->where('importUrl', route('runs.import')));
});

/*
 * SCREEN-024's structured preferences (the D18b slice plan). The blob is one row keyed `settings`
 * whose JSON carries exactly `Preference::SETTINGS_KEYS`; the cases below pin the round trip, each
 * key's own vocabulary, and the nested key-set refusal the top level already makes.
 */

it('round-trips the structured preferences and reads them back on the screen', function (): void {
    test()->put('/preferences', [
        'theme' => 'light',
        'failure_estimate' => 'off',
        'settings' => [
            'default_scenario' => 'unity_cup',
            'recommendation_aggressiveness' => 'balanced',
            'stat_target_defaults' => ['Speed' => 600, 'Stamina' => 700, 'Power' => 500, 'Guts' => 400, 'Wit' => 300],
            'language' => 'en',
        ],
    ])->assertRedirect();

    $settings = Preference::settings();

    expect($settings['default_scenario'])->toBe('unity_cup')
        ->and($settings['recommendation_aggressiveness'])->toBe('balanced')
        ->and($settings['stat_target_defaults'])->toBe(['Speed' => 600, 'Stamina' => 700, 'Power' => 500, 'Guts' => 400, 'Wit' => 300])
        ->and($settings['language'])->toBe('en');

    test()->get('/preferences')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('settings.default_scenario', 'unity_cup')
            ->where('settings.recommendation_aggressiveness', 'balanced')
            ->where('settings.stat_target_defaults.Speed', 600)
            ->where('settings.language', 'en')
            // One option per entry the matrix composes, in the matrix's own order, so the control
            // cannot offer a scenario the config does not carry (D-240).
            ->where('scenarioOptions', fn ($options): bool => collect($options)->every(
                static fn (array $option): bool => array_key_exists($option['value'], (array) config('scenarios.scenarios')),
            ))
            ->where('hardCap', 2000)
            ->where('exportUrl', route('preferences.export'))
            ->where('backupUrl', route('preferences.backup')));
});

it('stores a sparse blob and drops the row when every structured key is cleared', function (): void {
    // Only two of the four keys are sent with values, so the blob holds two: an unset preference
    // is absent, not a null to store — the model's own rule for absence.
    test()->put('/preferences', [
        'theme' => 'light',
        'failure_estimate' => 'off',
        'settings' => [
            'default_scenario' => 'ura_finale',
            'recommendation_aggressiveness' => null,
            'stat_target_defaults' => null,
            'language' => 'en',
        ],
    ])->assertRedirect();

    expect(Preference::settings())->toBe(['default_scenario' => 'ura_finale', 'language' => 'en']);

    // Clearing everything is the same claim the theme's follow-the-OS makes: no row at all.
    test()->put('/preferences', [
        'theme' => 'light',
        'failure_estimate' => 'off',
        'settings' => [
            'default_scenario' => null,
            'recommendation_aggressiveness' => null,
            'stat_target_defaults' => null,
            'language' => null,
        ],
    ])->assertRedirect();

    expect(Preference::whereKey('settings')->exists())->toBeFalse()
        ->and(Preference::settings())->toBe([]);
});

it('refuses a default scenario the matrix does not compose', function (): void {
    test()->put('/preferences', [
        'theme' => 'light',
        'failure_estimate' => 'off',
        'settings' => ['default_scenario' => 'ura_classic'],
    ])->assertSessionHasErrors('settings.default_scenario');

    expect(Preference::settings())->toBe([]);
});

it('refuses a stat-target default above the engine ceiling or below zero', function (): void {
    $targets = static fn (mixed $speed): array => [
        'Speed' => $speed, 'Stamina' => 600, 'Power' => 600, 'Guts' => 600, 'Wit' => 600,
    ];

    test()->put('/preferences', [
        'theme' => 'light',
        'failure_estimate' => 'off',
        'settings' => ['stat_target_defaults' => $targets(2001)],
    ])->assertSessionHasErrors('settings.stat_target_defaults.Speed');

    test()->put('/preferences', [
        'theme' => 'light',
        'failure_estimate' => 'off',
        'settings' => ['stat_target_defaults' => $targets(-1)],
    ])->assertSessionHasErrors('settings.stat_target_defaults.Speed');

    expect(Preference::settings())->toBe([]);
});

it('refuses a stat-target map that is not exactly the matrix\'s stats', function (): void {
    // A partial map would look like a complete set of defaults, and an unknown key would be a
    // stat this tool does not hold. `BuildTargetPayload::targets()` makes the same refusal for
    // the same reason, so the two cannot disagree about what a complete map is.
    test()->put('/preferences', [
        'theme' => 'light',
        'failure_estimate' => 'off',
        'settings' => ['stat_target_defaults' => ['Speed' => 600, 'Stamina' => 600]],
    ])->assertSessionHasErrors('settings.stat_target_defaults');

    test()->put('/preferences', [
        'theme' => 'light',
        'failure_estimate' => 'off',
        'settings' => ['stat_target_defaults' => ['Speed' => 600, 'Stamina' => 600, 'Power' => 600, 'Guts' => 600, 'Wit' => 600, 'Technique' => 1]],
    ])->assertSessionHasErrors('settings.stat_target_defaults');

    expect(Preference::settings())->toBe([]);
});

it('refuses an aggressiveness value outside the three the screen offers', function (): void {
    test()->put('/preferences', [
        'theme' => 'light',
        'failure_estimate' => 'off',
        'settings' => ['recommendation_aggressiveness' => 'reckless'],
    ])->assertSessionHasErrors('settings.recommendation_aggressiveness');

    expect(Preference::settings())->toBe([]);
});

it('refuses a language other than the one the corpus is labelled in', function (): void {
    test()->put('/preferences', [
        'theme' => 'light',
        'failure_estimate' => 'off',
        'settings' => ['language' => 'ja'],
    ])->assertSessionHasErrors('settings.language');

    expect(Preference::settings())->toBe([]);
});

it('refuses a settings key the preference matrix does not list', function (): void {
    // The blob's key set is a contract in exactly the way the top level's is, and `validated()`
    // would discard an unknown nested key just as silently.
    test()->put('/preferences', [
        'theme' => 'light',
        'failure_estimate' => 'off',
        'settings' => ['race_risk_thresholds' => ['low' => 10]],
    ])->assertSessionHasErrors('settings');

    expect(Preference::settings())->toBe([])->and(Preference::whereKey('settings')->exists())->toBeFalse();
});

it('pre-fills the setup wizard from the stored preferences and not from nothing', function (): void {
    Preference::putSettings(['default_scenario' => 'unity_cup', 'stat_target_defaults' => ['Speed' => 700]]);

    // Scenario Select pre-selects the stored default when the draft names no scenario.
    test()->get(route('career.scenario'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('selected', 'unity_cup'));

    // And Build Target pre-fills the one stat a default exists for, with no target stored.
    test()->get(route('career.target'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('target', null)
            ->where('statDefaults.Speed', 700));

    // A draft that names a scenario wins over the preference, because a career in progress is
    // what the Trainer chose for it.
    test()->withSession([SetupDraft::SESSION_KEY => ['scenario' => 'ura_finale']])
        ->get(route('career.scenario'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('selected', 'ura_finale'));
});

it('streams this tool\'s data as a JSON download carrying the four keys the export promises', function (): void {
    $run = TrainingRun::factory()->create([
        'umamusume_id' => Umamusume::factory()->state(['name' => 'Rice Shower']),
    ]);
    TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 1]);
    // The Veteran points at the run this test created, rather than letting its factory file
    // another career: the export's run count is then one run the test made, not one it cannot
    // account for.
    Veteran::factory()->create(['training_run_id' => $run->id]);
    Preference::putSettings(['default_scenario' => 'ura_finale']);

    $response = $this->get(route('preferences.export'))->assertOk();

    // Streaming, not a buffered download: the response is the framework's streamed kind, so the
    // payload was written through the output buffer rather than held as one string, and it is
    // offered as a download rather than rendered.
    expect($response->baseResponse)->toBeInstanceOf(StreamedResponse::class)
        ->and(str_contains((string) $response->headers->get('Content-Disposition'), 'trainer-desk-export-'))->toBeTrue();

    // A streamed response holds no body, so the shape is read by running the streaming callback
    // itself under an output buffer — the same closure the browser's download runs, and the reason
    // no temporary file exists on either path.
    $callback = $response->baseResponse->getCallback();

    ob_start();
    $callback();
    $payload = json_decode((string) ob_get_clean(), true, 512, JSON_THROW_ON_ERROR);

    expect(array_keys($payload))->toBe(['training_runs', 'turn_entries', 'veterans', 'preferences'])
        ->and(count($payload['training_runs']))->toBe(1)
        ->and(count($payload['turn_entries']))->toBe(1)
        ->and(count($payload['veterans']))->toBe(1)
        ->and($payload['preferences']['settings'])->toBe(['default_scenario' => 'ura_finale']);
});

it('refuses to write a snapshot when the connection has no file to snapshot', function (): void {
    // `phpunit.xml` forces `DB_DATABASE=:memory:`, and an in-memory database has no file to copy.
    // The guard is the true answer for that state, and the reason the suite can prove the refusal
    // directly but has to point the config at a file to prove the copy.
    $before = glob(storage_path('app/backups/uma-backup-*.sqlite')) ?: [];

    expect(fn () => test()->withoutExceptionHandling()->post(route('preferences.backup')))
        ->toThrow(RuntimeException::class, 'No SQLite database file to back up');

    // Differential, because the backups directory is shared with the real app: the claim is that
    // this request added nothing, not that nothing has ever been written.
    expect(glob(storage_path('app/backups/uma-backup-*.sqlite')) ?: [])->toBe($before);
});

it('writes a server-side backup that opens as a sqlite database carrying the file it copied', function (): void {
    // The action opens its own connection to the configured file, so this test only has to point
    // the config at a file it owns. It must not purge the application's connection, which for
    // `:memory:` destroys the schema every later test in the process reuses, and it must not
    // migrate: `migrate:fresh` ends in a `VACUUM`, which SQLite refuses inside the test's open
    // transaction. The config is rebuilt per test, so nothing here needs restoring.
    $source = storage_path('app/backups/preference-backup-source.sqlite');
    @unlink($source);

    $handle = new PDO('sqlite:'.$source);
    $handle->exec('create table "migrations" ("id" integer primary key, "migration" text, "batch" integer)');
    $handle->exec('create table "training_runs" ("id" integer primary key, "scenario" text)');
    $handle->exec("insert into \"training_runs\" (\"scenario\") values ('unity_cup')");
    $handle->exec("insert into \"migrations\" (\"migration\", \"batch\") values ('2026_09_27_121500_create_preferences_table', 1)");
    $handle = null;

    config(['database.connections.sqlite.database' => $source]);

    $before = glob(storage_path('app/backups/uma-backup-*.sqlite')) ?: [];

    try {
        $this->post(route('preferences.backup'))->assertRedirect();

        $added = array_values(array_diff(glob(storage_path('app/backups/uma-backup-*.sqlite')) ?: [], $before));

        expect($added)->toHaveCount(1);

        $copy = (string) $added[0];

        // A copy that will not open is not a backup, and one that opens without the source's
        // content is a template rather than a snapshot.
        $snapshot = new PDO('sqlite:'.$copy);
        $carried = (int) $snapshot->query("select count(*) from \"training_runs\" where \"scenario\" = 'unity_cup'")->fetchColumn();
        $rows = (int) $snapshot->query('select count(*) from "migrations"')->fetchColumn();
        $message = session('status');

        // The handle has to be released before the unlink: on Windows an open sqlite handle keeps
        // the file locked and the cleanup below would silently leave one snapshot per run behind.
        $snapshot = null;

        expect($carried)->toBe(1)
            ->and($rows)->toBe(1)
            // The flash carries the path, because where a server-side snapshot went is the one
            // thing a Trainer needs to know about it.
            ->and($message)->toBeString()
            ->and(str_contains((string) $message, $copy))->toBeTrue();
    } finally {
        @unlink($source);

        foreach (array_values(array_diff(glob(storage_path('app/backups/uma-backup-*.sqlite')) ?: [], $before)) as $file) {
            @unlink((string) $file);
        }
    }
});
