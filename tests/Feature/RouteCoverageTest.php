<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
 * The route coverage map (plan slice 8.8c).
 *
 * The claim this guard makes is narrow and mechanical: every live route that renders a page in this
 * agent's territory has at least one axe scan somewhere in the browser suite. It is the guard the
 * 8.8 measurement argued for, and the reason it is a static test rather than a browser test is that
 * the question is about the corpus, not about a page: a route with no scan is a hole in the suite
 * whether or not the page happens to be clean today.
 *
 * Three things it has to get right, each of which was a real failure mode in the measurement that
 * produced it:
 *
 *  - **Per case, not per file.** A spec file that visits five routes and carries one scan says
 *    nothing about the other four. The unit of coverage is the CASE, and the unit of the case's
 *    route is the LAST route it reached before scanning (Rule 43: an axe scan covers only the page
 *    it was taken on).
 *
 *  - **Both render shapes.** `SkillController::renderPage('Database/Skills', ...)` is not
 *    `Inertia::render('Database/Skills', ...)`; a pattern that only knows the latter loses
 *    `Database/Skills`, `Database/Supports` and `Database/Trainees` - three of the fourteen routes -
 *    and the floor below would quietly become eleven.
 *
 *  - **Both render shapes validated against the live page set.** A render argument counts as a page
 *    only if a `.vue` file for it exists in `resources/js/pages`, so a controller that happens to
 *    contain an unrelated `'Foo/Bar'` string is not read as a page route, and a page that is added
 *    without a route or a spec is read as a gap rather than ignored.
 *
 * The scope is this agent's page directories. A Career route that lacks a scan must not red this
 * guard: it would be fireable but not fixable from here, which defeats the guard's purpose. The
 * main agent extends the scope when they land their own coverage.
 */

/**
 * The page directories this guard covers. A page route is in scope when its page's first segment
 * is one of these.
 */
final class RouteCoverageMap
{
    public const SCOPE_DIRECTORIES = ['Database', 'Review', 'Catalog', 'Skills', 'SupportCards'];

    /**
     * Pages excluded from the scope, by exact page name.
     *
     * `Catalog/Show` is excluded: coord-before-touch (7.15), because the "Start career" handoff
     * lands there, and the scan for it lives in the peer's `catalog-detail.spec.ts`. Including it
     * would make this guard fireable but not fixable from this side. When 7.15 lands, the peer adds
     * Show to the scope - or this exclusion is lifted, and the guard starts asking about it.
     */
    public const EXCLUDED_PAGES = [
        'Catalog/Show',
    ];

    /**
     * The floor under the scoped route set.
     *
     * It is a diagnostic, not the contract: it catches the route list or the page walk collapsing
     * and returning nothing, so a green Assertion 1 cannot mean "there were no routes to check".
     * The per-route assertions are the contract.
     */
    public const SCOPE_FLOOR = 14;

    /**
     * The scoped page routes, as uri => page name.
     *
     * @return array<string, string>
     */
    public static function scopedPageRoutes(): array
    {
        $pages = self::pageSet();
        $scoped = [];

        foreach (Route::getRoutes() as $route) {
            if (! in_array('GET', $route->methods(), true)) {
                continue;
            }

            $action = $route->getActionName();
            if (! str_contains($action, '@')) {
                continue; // a closure route: no controller, no page
            }

            [$class, $method] = explode('@', $action, 2);
            $page = self::pageFor($class, $method);

            if ($page === null) {
                continue;
            }

            if (in_array($page, self::EXCLUDED_PAGES, true)) {
                continue;
            }

            if (! in_array(explode('/', $page)[0], self::SCOPE_DIRECTORIES, true)) {
                continue;
            }

            if (! isset($pages[$page])) {
                continue; // the render argument is not a page in this tree
            }

            $scoped[$route->uri()] = $page;
        }

        ksort($scoped);

        return $scoped;
    }

    /**
     * The page routes excluded by name, so the report can name what the scope left out.
     *
     * @return array<string, string>
     */
    public static function excludedPageRoutes(): array
    {
        $excluded = [];

        foreach (Route::getRoutes() as $route) {
            if (! in_array('GET', $route->methods(), true)) {
                continue;
            }

            $action = $route->getActionName();
            if (! str_contains($action, '@')) {
                continue;
            }

            [$class, $method] = explode('@', $action, 2);
            $page = self::pageFor($class, $method);

            if ($page !== null && in_array($page, self::EXCLUDED_PAGES, true)) {
                $excluded[$route->uri()] = $page;
            }
        }

        ksort($excluded);

        return $excluded;
    }

    /**
     * The routes the browser corpus reaches, as literal paths.
     *
     * Every literal `page.goto` counts, not only a case's first one: this map answers "does any
     * spec reach this route", and a route visited in the middle of a case is still reached.
     *
     * The keys are the LITERAL paths a spec navigated to, which may be an instantiation of a
     * parameterised route: `/support-cards/1` for the route `support-cards/{card}`. A route is
     * matched against them by pattern, not by string equality.
     *
     * @return array<int, string>
     */
    public static function visitedRoutes(): array
    {
        $visited = [];

        foreach (self::specSources() as $source) {
            if (preg_match_all("/page\.goto\(\s*['\"]([^'\"]+)['\"]/", $source, $matches)) {
                foreach ($matches[1] as $url) {
                    $visited[] = self::normalise($url);
                }
            }
        }

        return array_values(array_unique($visited));
    }

    /**
     * The literal paths the browser corpus scans.
     *
     * A route is covered when some case reaches it and that case carries a scan. The route a case's
     * scan belongs to is the last route it reached before scanning, so a case that visits two pages
     * and scans at the end covers the second.
     *
     * @return array<int, string>
     */
    public static function scannedRoutes(): array
    {
        $scanned = [];

        foreach (self::specSources() as $source) {
            foreach (self::caseBodies($source) as $body) {
                if (self::scanOffset($body) === null) {
                    continue;
                }

                $route = self::routeBeforeScan($body);

                if ($route !== null) {
                    $scanned[] = $route;
                }
            }
        }

        return array_values(array_unique($scanned));
    }

    /**
     * The pattern a route's path matches, so a literal navigation can be compared with it.
     *
     * A route's `{parameter}` is one path segment, so `support-cards/{card}` matches
     * `/support-cards/1` and `skills/{skill}` matches `/skills/1`. Without this, every
     * parameterised route would read as reached by no spec and scanned by no spec - and the two
     * catalogue detail routes are exactly that shape.
     */
    public static function routePattern(string $uri): string
    {
        $pattern = '';

        foreach (preg_split('/(\{[^}]+\})/', $uri, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [] as $segment) {
            $pattern .= preg_match('/^\{[^}]+\}$/', $segment) === 1
                ? '[^/]+'
                : preg_quote($segment, '#');
        }

        return '#^'.$pattern.'$#';
    }

    /**
     * Whether any of the given paths is an instance of the route.
     *
     * @param  array<int, string>  $paths
     */
    public static function routeIsReached(string $uri, array $paths): bool
    {
        $pattern = self::routePattern($uri);

        foreach ($paths as $path) {
            if (preg_match($pattern, $path) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * The page set, as page name => true, walked from the filesystem.
     *
     * The walk is `RecursiveIteratorIterator` rather than `glob('**')` because PHP's glob does not
     * expand `**`: a glob-based walk would scan one directory and pass without having looked. That
     * is the same reason the 1.12 guard walks the spec directory the way it does.
     *
     * @return array<string, bool>
     */
    private static function pageSet(): array
    {
        $pages = [];
        $root = resource_path('js/pages');

        $walk = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($walk as $file) {
            if (! str_ends_with($file->getFilename(), '.vue')) {
                continue;
            }

            $relative = substr($file->getPathname(), strlen($root) + 1);
            $name = substr($relative, 0, -4); // strip ".vue"
            $pages[str_replace(DIRECTORY_SEPARATOR, '/', $name)] = true;
        }

        return $pages;
    }

    /**
     * The page a controller method renders, or null.
     *
     * Reads the method's own body, so a private helper that renders another page does not
     * misattribute its page to the public method that calls it.
     */
    private static function pageFor(string $class, string $method): ?string
    {
        // The controller's own path, subdirectory and all: `App\Http\Controllers\Career\X` is
        // `app/Http/Controllers/Career/X.php`, and dropping the subdirectory would leave every
        // Career route looking like it had no controller.
        $relative = Str::after($class, 'App\\Http\\Controllers\\');
        $file = base_path('app/Http/Controllers/'.str_replace('\\', '/', $relative).'.php');

        if (! is_file($file)) {
            return null;
        }

        $source = (string) file_get_contents($file);
        $offset = strpos($source, 'public function '.$method.'(');

        if ($offset === false) {
            return null;
        }

        $body = substr($source, $offset);

        // Cut the method at the next declaration, which is one indentation level in from the left.
        // PREG_OFFSET_CAPTURE is what makes `$cut[0][1]` an offset; without it, that index is the
        // second CHARACTER of the match, and the body would be truncated to nothing.
        if (preg_match('/\n    (?:public|private|protected) function /', $body, $cut, PREG_OFFSET_CAPTURE)) {
            $body = substr($body, 0, (int) $cut[0][1]);
        }

        if (preg_match('/(?:Inertia::render|renderPage)\(\s*[\'"]([A-Za-z]+\/[A-Za-z]+)[\'"]/', $body, $page)) {
            return $page[1];
        }

        return null;
    }

    /**
     * The browser spec sources, read from the filesystem.
     *
     * @return array<int, string>
     */
    private static function specSources(): array
    {
        $sources = [];
        $root = base_path('tests/browser');

        $walk = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($walk as $file) {
            if (! str_ends_with($file->getFilename(), '.spec.ts')) {
                continue;
            }

            $sources[] = (string) file_get_contents($file->getPathname());
        }

        return $sources;
    }

    /**
     * The bodies of the spec's test cases.
     *
     * A case opens at a `test(` with a quoted name and runs until the next one. `test.describe(`
     * is not a case, and `test.skip(` is - a skipped case still carries assertions.
     *
     * @return array<int, string>
     */
    private static function caseBodies(string $source): array
    {
        $pattern = '/\btest(?:\.(?:skip|fixme|only))?\s*\(\s*[\'"`]/';

        if (! preg_match_all($pattern, $source, $matches, PREG_OFFSET_CAPTURE)) {
            return [];
        }

        $bodies = [];
        $count = count($matches[0]);

        for ($i = 0; $i < $count; $i++) {
            $start = $matches[0][$i][1];
            $end = $i + 1 < $count ? $matches[0][$i + 1][1] : strlen($source);
            $bodies[] = substr($source, $start, $end - $start);
        }

        return $bodies;
    }

    /**
     * Where a case's scan call is, or null when it carries none.
     */
    private static function scanOffset(string $body): ?int
    {
        $offsets = [];

        foreach (['buildAxe(page).analyze()', 'toHaveNoViolations'] as $needle) {
            $at = strpos($body, $needle);

            if ($at !== false) {
                $offsets[] = $at;
            }
        }

        return $offsets === [] ? null : min($offsets);
    }

    /**
     * The route a case's scan belongs to: the last route it reached before scanning.
     */
    private static function routeBeforeScan(string $body): ?string
    {
        $scanAt = self::scanOffset($body);
        $before = $scanAt === null ? $body : substr($body, 0, $scanAt);

        if (! preg_match_all("/page\.goto\(\s*['\"]([^'\"]+)['\"]/", $before, $matches)) {
            return null;
        }

        $urls = $matches[1];

        return self::normalise((string) end($urls));
    }

    /**
     * A URL to the route it addresses: no query string, no leading slash.
     */
    private static function normalise(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);

        return ltrim(is_string($path) && $path !== '' ? $path : $url, '/');
    }
}

/*
 * The floor is a separate case so that "the scoped route list collapsed" is its own failure, naming
 * itself, rather than a precondition buried in an assertion that would otherwise report no gaps.
 */
test('the scoped page route set did not collapse', function () {
    $scoped = RouteCoverageMap::scopedPageRoutes();

    expect(count($scoped))
        ->toBeGreaterThanOrEqual(
            RouteCoverageMap::SCOPE_FLOOR,
            'the scoped route list collapsed (expected >= '.RouteCoverageMap::SCOPE_FLOOR.', got '.count($scoped).'); the per-route assertions did not run on the expected set',
        );
});

test('every scoped page route has an axe scan', function () {
    $scoped = RouteCoverageMap::scopedPageRoutes();
    $scanned = RouteCoverageMap::scannedRoutes();

    $gaps = [];

    foreach ($scoped as $uri => $page) {
        if (! RouteCoverageMap::routeIsReached($uri, $scanned)) {
            $gaps[] = $uri;
        }
    }

    expect($gaps)->toBe([], 'routes without an axe scan: '.implode(', ', $gaps));
});

test('every scoped page route is visited by some spec', function () {
    $scoped = RouteCoverageMap::scopedPageRoutes();
    $visited = RouteCoverageMap::visitedRoutes();

    $unvisited = [];

    foreach ($scoped as $uri => $page) {
        if (! RouteCoverageMap::routeIsReached($uri, $visited)) {
            $unvisited[] = $uri;
        }
    }

    expect($unvisited)->toBe([], 'routes no spec reaches: '.implode(', ', $unvisited));
});
