<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'app' => [
                'name' => config('app.name'),
                'version' => $this->appVersion(),
                // No source defines a Global ruleset version (docs/proposals/design-2.0.md §48),
                // so this is a named absence, never an invented number.
                'ruleset' => null,
            ],
        ];
    }

    /**
     * The application version, read from `VERSION.md` (its single source). A missing file or an
     * unparseable line yields null, which the shell renders as an absence rather than a guess.
     */
    private function appVersion(): ?string
    {
        $path = base_path('VERSION.md');

        if (! is_file($path)) {
            return null;
        }

        $contents = (string) file_get_contents($path);

        if (preg_match('/Current version:\s*`([^`]+)`/', $contents, $matches) !== 1) {
            return null;
        }

        return $matches[1];
    }
}
