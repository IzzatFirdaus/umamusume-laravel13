<?php

declare(strict_types=1);

use App\Providers\NativeAppServiceProvider;

return [
    /*
     * The version of your app. It is used to determine if the app needs to be
     * updated. Increment this value every time you release a new version.
     */
    'version' => env('NATIVEPHP_APP_VERSION', '1.0.0'),

    /*
     * The ID of your application. This should be a unique identifier, usually in
     * the form of a reverse domain name.
     */
    'app_id' => env('NATIVEPHP_APP_ID', 'com.trainerdesk.app'),

    'deeplink_scheme' => env('NATIVEPHP_DEEPLINK_SCHEME'),

    'author' => env('NATIVEPHP_APP_AUTHOR', 'Trainer Desk'),

    'copyright' => env('NATIVEPHP_APP_COPYRIGHT'),

    'description' => env('NATIVEPHP_APP_DESCRIPTION', 'Trainer Desk - Umamusume Pretty Derby career planner'),

    'website' => env('NATIVEPHP_APP_WEBSITE'),

    /*
     * The default service provider for your application. This provider takes care
     * of bootstrapping your application and configuring any global hotkeys,
     * menus, windows, etc.
     */
    'provider' => NativeAppServiceProvider::class,

    /*
     * A list of environment keys that should be removed from the .env file when
     * the application is bundled for production. Wildcards are supported.
     */
    'cleanup_env_keys' => [
        'AWS_*',
        'AZURE_*',
        'GITHUB_*',
        'DO_SPACES_*',
        '*_SECRET',
        'BIFROST_*',
        'NATIVEPHP_UPDATER_PATH',
        'NATIVEPHP_APPLE_ID',
        'NATIVEPHP_APPLE_ID_PASS',
        'NATIVEPHP_APPLE_TEAM_ID',
        'NATIVEPHP_AZURE_PUBLISHER_NAME',
        'NATIVEPHP_AZURE_ENDPOINT',
        'NATIVEPHP_AZURE_CERTIFICATE_PROFILE_NAME',
        'NATIVEPHP_AZURE_CODE_SIGNING_ACCOUNT_NAME',
    ],

    /*
     * A list of files and folders that should be removed from the final app before
     * it is bundled for production. Glob / wildcard patterns are supported.
     */
    'cleanup_exclude_files' => [
        'build',
        'temp',
        'content',
        'node_modules',
        '*/tests',
    ],

    'updater' => [
        /*
         * The updater only works when your application is bundled for production.
         */
        'enabled' => env('NATIVEPHP_UPDATER_ENABLED', false),

        'default' => env('NATIVEPHP_UPDATER_PROVIDER', 'spaces'),

        'providers' => [
            'github' => [
                'driver' => 'github',
                'repo' => env('GITHUB_REPO'),
                'owner' => env('GITHUB_OWNER'),
                'token' => env('GITHUB_TOKEN'),
                'vPrefixedTagName' => env('GITHUB_V_PREFIXED_TAG_NAME', true),
                'private' => env('GITHUB_PRIVATE', false),
                'autoupdate_token' => env('GITHUB_AUTOUPDATE_TOKEN'),
                'channel' => env('GITHUB_CHANNEL', 'latest'),
                'releaseType' => env('GITHUB_RELEASE_TYPE', 'draft'),
            ],

            's3' => [
                'driver' => 's3',
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
                'region' => env('AWS_DEFAULT_REGION'),
                'bucket' => env('AWS_BUCKET'),
                'endpoint' => env('AWS_ENDPOINT'),
                'path' => env('NATIVEPHP_UPDATER_PATH', null),
                'public_url' => env('AWS_PUBLIC_URL'),
            ],

            'spaces' => [
                'driver' => 'spaces',
                'key' => env('DO_SPACES_KEY_ID'),
                'secret' => env('DO_SPACES_SECRET_ACCESS_KEY'),
                'name' => env('DO_SPACES_NAME'),
                'region' => env('DO_SPACES_REGION'),
                'path' => env('NATIVEPHP_UPDATER_PATH', null),
            ],
        ],
    ],

    'queue_workers' => [],

    'prebuild' => [
        'npm run build',
    ],

    'postbuild' => [
    ],

    'nsis' => [
        'delete_app_data_on_uninstall' => env('NATIVEPHP_NSIS_DELETE_APP_DATA', false),
    ],

    'binary_path' => env('NATIVEPHP_PHP_BINARY_PATH'),
];
